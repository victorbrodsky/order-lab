#!/usr/bin/env python3


print('Hello World!')

from puresasl.client import SASLClient

sasl = SASLClient('somehost2', 'customprotocol')
conn = get_connection_to('somehost2')
available_mechs = conn.get_mechanisms()
sasl.choose_mechanism(available_mechs, allow_anonymous=False)
while True:
    status, challenge = conn.get_challenge()
    if status == 'COMPLETE':
        break
    elif status == 'OK':
        response = sasl.process(challenge)
        conn.send_response(response)
    else:
        raise Exception(status)

if not sasl.complete:
    raise Exception("SASL negotiation did not complete")

# begin normal communication
encoded = conn.fetch_data()
decoded = sasl.unwrap(encoded)
response = process_data(decoded)
conn.send_data(sasl.wrap(response))
