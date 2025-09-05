#!/usr/bin/python3
import logging
import os
import requests

def send_confirmation_email(msg):
    #http://127.0.0.1/directory/send-confirmation-email/
    #https://view.online/c/test-institution/test-department/directory/send-confirmation-email/
    #url = 'http://127.0.0.1/directory/send-confirmation-email'
    url = 'https://view.online/c/test-institution/test-department/directory/send-confirmation-email/'
    response = requests.get(url,verify=False)
    if response.status_code == 200:
        print(f"Email triggered successfully! Status code: {response.status_code}")
    else:
        print(f"Failed to trigger email. Status code: {response.status_code}")

def main():
    logger = logging.getLogger(__name__)
    # logger.setLevel(logging.INFO)
    logger.setLevel(logging.DEBUG)
    # handler = logging.StreamHandler()

    # Create file handler
    handler = logging.FileHandler('/srv/order-lab-tenantapptest/orderflex/var/backup/python_restore_db.log')
    handler.setLevel(logging.DEBUG)

    formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    handler.setFormatter(formatter)
    logger.addHandler(handler)

    print("Testing started")

    send_confirmation_email('Testing')
    logger.info("Logger Testing finished")
    print("Testing finished")


if __name__ == '__main__':
    main()
