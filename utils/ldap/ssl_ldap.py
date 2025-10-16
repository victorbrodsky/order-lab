#Option A

# ad_test_ldaps_ntlm.py
from ldap3 import Server, Connection, Tls, NTLM, SUBTREE, ALL
import os, sys, ast

HOST    = os.getenv('LDAP_HOST', 'BJCNTDC07.bjc-nt.bjc.org')
PORT    = int(os.getenv('LDAP_PORT', '636'))
CAFILE  = os.getenv('LDAP_CA_FILE', '/tmp/bjc-bundle.pem')

# DOMAIN\user (or UPN works too)
USER    = os.getenv('LDAP_NTLM_USER', r'ACCOUNTS\PATH-SVC-BindUser')
PWD     = os.getenv('LDAP_NTLM_PASS')
BASE_DN = os.getenv('LDAP_BASE_DN', 'DC=bjc-nt,DC=bjc,DC=org')

# Example user search filter
FILTER  = os.getenv('LDAP_USER_FILTER', '(|(sAMAccountName=vxb3670)(cn=vxb3670))')

# Allow referral targets (host, require_ssl)
ALLOWED_REFERRALS_ENV = os.getenv('LDAP_ALLOWED_REFERRALS')
if ALLOWED_REFERRALS_ENV:
    try:
        allowed_referral_hosts = list(ast.literal_eval(ALLOWED_REFERRALS_ENV))
    except Exception as e:
        sys.exit(f"Invalid LDAP_ALLOWED_REFERRALS literal: {e}")
else:
    allowed_referral_hosts = [('accounts.ad.wustl.edu', True)]

if not PWD:
    sys.exit("Set LDAP_NTLM_PASS")

tls = Tls(validate=2, ca_certs_file=CAFILE)

# conn = Connection(
#     srv,
#     user=USER,
#     password=PWD,
#     authentication=NTLM,
#     auto_bind=True,
#     auto_referrals=True,
#     allowed_referral_hosts=[('accounts.ad.wustl.edu',True)], #allowed_referral_hosts,
#     raise_exceptions=True,
# )

server = Server('bjc-nt.bjc.org',use_ssl=True,get_info=ALL,allowed_referral_hosts=[('accounts.ad.wustl.edu',True)])
with Connection(server,user='accounts\Path-SVC-BindUser',password=PWD,auto_referrals=True,authentication=NTLM) as conn:
    conn.search('DC=bjc-nt,DC=bjc,DC=org',f'(cn={USER})',attributes=['*'])
    try:
        dn = conn.response[0]['attributes']['distinguishedName']
    except:
        print("Username incorrect. Please try again...")
        sys.exit("exits the whole script")
    new_data={
        'username':USER,
        'displayName' : conn.response[0]['attributes']['displayName'],
        'is_auth':True
    }
    try:
        with Connection(server, dn, PWD) as conn2:
            print(f"Logged in as {new_data['displayName']}. Resource will display if user is authorized...",new_data)
            sys.exit("exits the whole script")
    except:
        print("Password incorrect. Please try again...")
        sys.exit("exits the whole script")

print("Bind OK. whoami:", conn.extend.standard.who_am_i())

# Use the BaseDN from your snippet:
conn.search(BASE_DN, FILTER, SUBTREE, attributes=['dn', 'userPrincipalName', 'displayName'])
for e in conn.response:
    attrs = e.get('attributes', {})
    print(e['dn'], attrs.get('userPrincipalName'), attrs.get('displayName'))

conn.unbind()
