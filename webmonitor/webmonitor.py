#!/usr/bin/env python
# Created by Oleg Ivanov


#Ref: https://gist.github.com/adeekshith/fef4ff9949b88ce102bd
#Ref: https://medium.com/swlh/tutorial-creating-a-webpage-monitor-using-python-and-running-it-on-a-raspberry-pi-df763c142dac

#https://www.shell-tips.com/linux/sudo-sorry-you-must-have-a-tty-to-run-sudo/#gsc.tab=0
#1) add permission to run systemctl by apache user by running visudo and add the following:
#2) Defaults:apache !requiretty
#3) apache ALL= NOPASSWD: /usr/bin/systemctl restart httpd.service
#   apache ALL= NOPASSWD: /usr/bin/systemctl restart postgresql-14
# Multitenancy with haproxy and php-fpm:
# apache ALL= NOPASSWD: /usr/bin/systemctl restart php-fpm
# apache ALL= NOPASSWD: /usr/bin/systemctl restart haproxy
# apache ALL= NOPASSWD: /usr/bin/systemctl restart httpdtenantapp1 (optional)

# sample usage: python webmonitor.py --urls "http://view.med.cornell.edu, http://view-test.med.cornell.edu" -h "smtp.med.cornell.edu" -u "" -p "" -s "oli2002@med.cornell.edu" -r "oli2002@med.cornell.edu"
# -l --urls: "http://view.med.cornell.edu,http://view-test.med.cornell.edu"
# -h --mailerhost: "smtp.med.cornell.edu"
# -u --maileruser: "sss@site.com"
# -p --mailerpassword: "mypassword"
# -e --env

import os
import sys, getopt, logging
import requests
import smtplib
from smtplib import SMTPException
from email.mime.text import MIMEText
import time
from datetime import datetime
import subprocess
from subprocess import PIPE
import urllib
#from urllib.parse import urlsplit, quote, urlunsplit
#import smtplib
#import yagmail
#from requests.exceptions import SSLError, RequestException

#sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'utils', 'scraper')))
#scraper_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'utils', 'scraper'))
#sys.path.insert(0, scraper_path)  # insert at beginning to prioritize

scraper_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'utils', 'scraper'))
sys.path.insert(0, scraper_path)
print("scraper_path=",scraper_path)
from check_webpage import Checker

#from ..utils.scraper.check_webpage import Checker

URL_TO_MONITOR = "" #change this to the URL you want to monitor
MAILER_HOST = ""
MAILER_PORT = ""
MAILER_USERNAME = ""
MAILER_PASSWORD = ""
RECEIVERS = []
SENDER = ""
ON_DOWN_COMMANDS = ""
URL_COMMAND = ""
ENV_NAME = "Unknown"

COMMAND_COUNTER = 0


def help():
    print(
        "Usage: python webmonitor.py [OPTION]... [FILE]...\n" \
        "If a resonse status is 200 - site is up, otherwise - site is down \n" \
        "\n" \
        "-l, --urls             comma separated urls to monitor\n" \
        "-h, --mailerhost       mailer host\n" \
        "-o, --mailerport       mailer port\n" \
        "-u, --maileruser       mailer username\n" \
        "-p, --mailerpassword   mailer password\n" \
        "-r, --receivers        comma separated receiver's emails\n" \
        "-s, --sender           sender's emails\n" \
        "-c, --command          console command to restore the server if url is not responding\n" \
        "-U, --urlcommand       url of the local server; try to restore the server if the same as current url\n" \
        " \n" \
        "-e, --env              environment info as a string attached to the notification email\n" \
        "-H, --help             this help"
    )


#TODO: test with expired SSL certificate
#use archive:
#cat
# /etc/letsencrypt/live/view.online/fullchain.pem
# /etc/letsencrypt/live/view.online/privkey.pem
# > /etc/letsencrypt/live/view.online/cert_key_expired.pem
def get_site_status(url, sendSuccEmail=False):
    #print("### get_site_status url="+url)
    #logging.info("get_site_status: url="+url)

    # if COMMAND_COUNTER > 0:
    #     return 'Stop: COMMAND_COUNTER='+COMMAND_COUNTER

    status = ''
    response = None

    # if is_url_accessible(url):
    #     print("URL is accessible.")
    #     sendEmail(url, 'up')
    #     return 'up'
    # else:
    #     print("URL is not accessible.")
    #     sendEmail(url, 'down')
    #     return 'down'

    #Testing
    #check_page(url)
    #verify_https_page(url)
    #sys.exit()

    try:
        #is expired, self-signed, or invalid,  will still succeed and return a 200 if the server responds
        response = requests.get(url,verify=False)  #Use SSL verification - require fullchain.pem certificate
        #response = requests.get(url, timeout=5)     #Don't use SSL verification - require only cert.pem
    except:
        #print('response=',response)
        print("Exception: status_code=" + str(response.status_code))
        #status = "Exception: requests.get("+url+")"
        #print('get_site_status: status='+status)
        sendEmail(url, 'down')
        #return 'down: except'
        return 'down'

    #print(response)
    #print("status_code="+str(response.status_code))
    #logging.info("status_code="+str(response.status_code))
    #print(response.headers)
    #print(response.content)

    if response != None:
        print("response is not NULL")
        try:
            print("before if")
            if getattr(response, 'status_code') == 200:
                print("response status_code = 220")
                #print("response.text=",response.text)
                # additional check if the web page is shown as expected (in case SSL certificate is invalid)
                # cehck fo 'Welcome to' or 'id="display-username"'
                # if 'id="display-username"' in response.text:
                # if 'Welcome to' in response.text:
                #     print(f"Element 'Welcome to' found => Site {url} is up")
                #     if sendSuccEmail == True:
                #         sendEmail(url, 'up')
                #     return 'up'

                # additional check if the web page is shown as expected (in case SSL certificate is invalid)
                # find element by ID heartbeatInput
                if web_page_checker(url) == True:
                    if sendSuccEmail == True:
                        sendEmail(url, 'up')
                    return 'up'

        except AttributeError:
            status = "Exception: AttributeError for " + url
            #sendEmail(url, 'down')
            #return 'down: except AttributeError'

    #site is down
    sendEmail(url, 'down')

    #run restart server command only once and only if url is the local server
    # if COMMAND_COUNTER == 0:
    #     if isLocalServer(url):
    #         #restartServer(url)
    #         pass

    #if status != '':
    #    status = "-" + status
    # return 'down'+status

    #print("get_site_status:","return=down","status=",status)
    return 'down'

#web page check for heartbeatInput is using selenium and root access to /usr/share/httpd/.local
#Therefore, it must be run by root cron: sudo crontab -l
def web_page_checker(url):
    print("###web_page_checker###")
    checker = Checker()
    return checker.check_element_on_webpage(url)

# #Testing
# def is_url_accessible(url):
#     try:
#         # This will verify SSL by default
#         #If using HaProxy => fail
#         #explicitly specifying the path to the certificate file using the verify parameter
#         #Use: response = requests.get(url, timeout=5, verify="/etc/letsencrypt/live/view.online/cert.pem")
#         #Python and  will use this path unless  is explicitly set. You can verify this by running:
#         # python -c "import ssl; print(ssl.get_default_verify_paths())"
#         #To add SSL_CERT_FILE permanently:
#         #1) nano ~/.bashrc
#         #2) export SSL_CERT_FILE=/etc/letsencrypt/live/view.online/cert.pem
#         #2) expects a complete chain: export SSL_CERT_FILE=/etc/letsencrypt/live/view.online/fullchain.pem
#         #3) source ~/.bashrc
#         # response = requests.get(
#         #     #url,
#         #     #verify="/etc/letsencrypt/live/view.online/cert_key.pem"
#         #     "https://view.online",
#         #     cert=("/etc/letsencrypt/live/view.online/fullchain.pem", "/etc/letsencrypt/live/view.online/privkey.pem"),
#         #     #cert=("/etc/letsencrypt/archive/view.online/fullchain2.pem", "/etc/letsencrypt/archive/view.online/privkey2.pem"),
#         #     #verify=True,  # or path to CA bundle
#         #     verify = "/etc/letsencrypt/live/view.online/cert.pem"
#         # )
#         response = requests.get(
#             "https://view.online",
#             cert=("/etc/letsencrypt/live/view.online/fullchain.pem", "/etc/letsencrypt/live/view.online/privkey.pem"),
#             verify="/etc/pki/ca-trust/extracted/pem/tls-ca-bundle.pem" #Use trusted CA
#             #verify = True
#         )
#
#         return response.status_code == 200
#     except requests.exceptions.SSLError as e:
#         print(f"SSL error: {e}")
#     except requests.exceptions.RequestException as e:
#         print(f"Request error: {e}")
#     return False
#
# #Testing
# def check_page(url):
#     try:
#         # Enforce SSL verification
#         response = requests.get(url, verify=True, timeout=5)
#         response.raise_for_status()
#
#         # Check for suspicious warning phrases in the page content
#         if "Warning: Potential Security Risk Ahead" in response.text:
#             print(f"⚠️ Security warning detected on {url}")
#         else:
#             print(f"✅ Site {url} is accessible and certificate is valid")
#     except SSLError as ssl_err:
#         print(f"❌ SSL certificate error on {url}: {ssl_err}")
#     except RequestException as req_err:
#         print(f"❌ Failed to access {url}: {req_err}")
#
# def verify_https_page(url):
#     try:
#         response = requests.get(url, timeout=5, verify=True)
#         response.raise_for_status()
#
#         # Check for browser-style warning content
#         warning_phrases = [
#             "Warning: Potential Security Risk Ahead",
#             "Your connection is not private",
#             "NET::ERR_CERT_AUTHORITY_INVALID",
#             "SEC_ERROR_UNKNOWN_ISSUER"
#         ]
#
#         if any(phrase in response.text for phrase in warning_phrases):
#             print(f"⚠️ Page loaded but contains security warning content: {url}")
#         else:
#             print(f"✅ Page is accessible and certificate is valid: {url}")
#
#     except SSLError as e:
#         print(f"❌ SSL error: {e}")
#     except ConnectionError as e:
#         print(f"❌ Connection error: {e}")
#     except Timeout:
#         print(f"❌ Timeout while trying to reach {url}")
#     except Exception as e:
#         print(f"❌ Unexpected error: {e}")

def sendEmail(url, status):
    #Remove http from url, somehow gmail has problem with urls in the email body or wcm filter them out
    #url = urlsplit(url).path
    url = url.split("://")[1]
    #print(f"url={url}")

    timestamp = datetime.now().strftime("%H:%M:%S %d-%m-%Y")

    if status == "up":
        # site is up
        emailSubject = f"[{timestamp}] Site {url} is accessible! (sent by webmonitor.py from {ENV_NAME})"
        emailBody = f"[{timestamp}] Site '{url}' is UP!"
        send_email_alert(SENDER, RECEIVERS, emailSubject, emailBody)
        return True

    # site is down
    emailSubject = f"[{timestamp}] Site {url} appears inaccessible!"

    #url = urlsplit(url).path
    emailBody = f"[{timestamp}] Site {url} does not appear to be accessible. Please verify the site is operational! \n\n Sent by the independent script webmonitor.py from {ENV_NAME}"
    send_email_alert(SENDER, RECEIVERS, emailSubject, emailBody)
    return False

def send_email_alert(fromEmail, toEmailList, emailSubject, emailBody):
    if MAILER_HOST == "":
        timestamp = datetime.now().strftime("%H:%M:%S %d-%m-%Y")
        print(f"[{timestamp}] Error: unable to send email: MAILER_HOST is not provided")
        return False
        
    if MAILER_HOST == "smtp.gmail.com":
        send_gmail_email_alert(fromEmail, toEmailList, emailSubject, emailBody)
    else:
        send_local_email_alert(fromEmail, toEmailList, emailSubject, emailBody);

def send_local_email_alert(fromEmail, toEmailList, emailSubject, emailBody):
    #print("send_local_email_alert:",fromEmail,toEmailList,emailSubject)
    timestamp = datetime.now().strftime("%H:%M:%S %d-%m-%Y")
    if MAILER_HOST == "":
        print(f"[{timestamp}] Error: unable to send local email: MAILER_HOST is not provided")
        return False
    emailBody = emailBody + "\n\n" + datetime.now().strftime('%Y-%B-%d %H:%M:%S')
    msg = MIMEText(emailBody)
    msg['Subject'] = emailSubject
    msg['From'] = fromEmail
    msg['To'] = ', '.join(toEmailList)

    try:
        #print("MAILER_HOST=" + MAILER_HOST+", MAILER_PORT="+MAILER_PORT)
        smtpObj = smtplib.SMTP(MAILER_HOST, MAILER_PORT)
        if MAILER_USERNAME != "" and MAILER_PASSWORD != "":
            smtpObj.starttls()          
            #smtpObj.login(MAILER_USERNAME, MAILER_PASSWORD)
            #urllib.request.pathname2url(stringToURLEncode)
            smtpObj.login(MAILER_USERNAME, urllib.request.pathname2url(MAILER_PASSWORD))
        smtpObj.sendmail(fromEmail, toEmailList, msg.as_string())
        print(f"[{timestamp}] Successfully sent local email: ",emailSubject)
    except SMTPException:
        print(f"[{timestamp}] Error: unable to send email: ",emailSubject)
        #pass

#https://mailtrap.io/blog/python-send-email-gmail/
def send_gmail_email_alert(fromEmail, toEmailList, emailSubject, emailBody):
    #print("send_gmail_email_alert:",fromEmail,toEmailList,emailSubject)
    timestamp = datetime.now().strftime("%H:%M:%S %d-%m-%Y")
    print(f"[{timestamp}] MAILER_HOST={MAILER_HOST}, MAILER_USERNAME={MAILER_USERNAME}, MAILER_PASSWORD={MAILER_PASSWORD}")
    if MAILER_HOST == "":
        print(f"[{timestamp}] Error: unable to send gmail email: MAILER_HOST is not provided")
        return False

    if MAILER_USERNAME == "":
        print(f"[{timestamp}] Error: unable to send gmail email: MAILER_USERNAME is not provided")
        return False

    if MAILER_PASSWORD == "":
        print(f"[{timestamp}] Error: unable to send gmail email: MAILER_PASSWORD is not provided")
        return False
    
    emailBody = emailBody + "\n\n" + datetime.now().strftime('%Y-%B-%d %H:%M:%S')
    msg = MIMEText(emailBody)
    msg['Subject'] = emailSubject
    msg['From'] = fromEmail
    msg['To'] = ', '.join(toEmailList)

    try:
        smtpObj = smtplib.SMTP_SSL('smtp.gmail.com', 465)
    except SMTPException:
        print(f"[{timestamp}] Error: unable to send gmail email: ",emailSubject)
    else:
        with smtpObj:
            smtpObj.login(MAILER_USERNAME, MAILER_PASSWORD)
            smtpObj.sendmail(fromEmail, toEmailList, msg.as_string())
            print(f"[{timestamp}] Successfully sent gmail email: ",emailSubject)

def restartServer(url):
    if ON_DOWN_COMMANDS != "":
        #ON_DOWN_COMMANDS is a string or comma seperated string of commands
        commands = ON_DOWN_COMMANDS.split(",")
        outputs = []
        for command in commands:
            #try to run command
            output = runCommand(command.strip())
            outputs.append(output)

        #Remove http from url, somehow gmail has problem with urls in the email body or wcm filter them out
        #url = urlsplit(url).path
        url = url.split("://")[1]

        # send email
        emailSubject = "Run restart commands in " + ENV_NAME
        emailBody = "Trying to run restart commands for " + url + " in " + ENV_NAME + ":\n" + ON_DOWN_COMMANDS
        emailBody = emailBody + "." + "\n\n Output=" + str(outputs)      
        send_email_alert(SENDER, RECEIVERS, emailSubject, emailBody)

        #inceremnt global command counter
        global COMMAND_COUNTER
        COMMAND_COUNTER = COMMAND_COUNTER + 1

#https://janakiev.com/blog/python-shell-commands/
#https://stackoverflow.com/questions/89228/how-do-i-execute-a-program-or-call-a-system-command
def runCommand(command):
    # try to restart the server
    print("run: " + command)
    #print(os.popen(command).read())
    #output = subprocess.run([command], capture_output=True) #capture_output is for python > 3.7
    output = subprocess.run([command], stdout=PIPE, stderr=PIPE, shell=True)
    print(output)
    # sleep in seconds
    #time.sleep(10)
    return output

def isLocalServer(url):
    # check if url is located on the local server
    #print("isLocalServer: ",URL_COMMAND,url)
    if URL_COMMAND == url:
        return True
    return False

def main(argv):
    #print("\n### webmonitor.py")
    #sys.exit(1)
    #print("\n### webmonitor.py "+datetime.now().strftime('%Y-%B-%d %H:%M:%S')+"###")
    #logging.basicConfig(filename='checksites.log',level=logging.INFO)
    #logging.info('main start')

    #response = requests.get('https://view-test.med.cornell.edu/',verify=False)
    #print(response)
    #sys.exit(2)

    # print("!!! Cache dir2:", os.environ.get('XDG_CACHE_HOME'))

    # # Set custom cache directory for selenium
    # # cache_dir = '/var/www/.cache'
    # cache_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'orderflex', 'var', 'cache'))
    # print(f"######## cache_dir={cache_dir}")
    # # cache_dir = '/srv/order-lab-tenantapptest/orderflex/var/'
    # os.environ['XDG_CACHE_HOME'] = cache_dir
    # # Ensure the cache directory exists
    # if not os.path.exists(cache_dir):
    #     print(f"create cache_dir={cache_dir}")
    #     try:
    #         os.makedirs(cache_dir, exist_ok=True)
    #         os.chown(cache_dir, os.getuid(), os.getgid())  # Optional: set ownership
    #     except PermissionError:
    #         print(f"Permission denied: cannot create {cache_dir}. Run script with proper privileges.")

    urls = ''           # -l
    mailerhost = ''     # -h
    mailerport = ''     # -o
    maileruser = ''     # -u
    mailerpassword = '' # -p
    receivers = ''      # -r
    sender = ''         # -s
    commands = ''        # -c
    urlcommand = ''     # -U
    env = ''            # -e

    try:
        opts, args = getopt.getopt(
            argv,
            "l:h:o:u:p:r:s:c:U:e:H",
            ["urls=", "mailerhost=", "mailerport=",
             "maileruser=", "mailerpassword=",
             "receivers=", "sender=",
             "commands=", "urlcommand=",
             "env=", "help"
             ]
        )
    except getopt.GetoptError:
        print('Parameters error')
        #logging.warning('Parameters error')
        #help()
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-l", "--urls"):                     # == '--urls':
            urls = arg
            #print('webmonitor.py --urls=' + urls)
        elif opt in ("-h", "--mailerhost"):             # == "--mailerhost":
            mailerhost = arg
        elif opt in ("-o", "--mailerport"):             # == "--mailerport":
            mailerport = arg
        elif opt in ("-u", "--maileruser"):             # == "--maileruser":
            maileruser = arg
        elif opt in ("-p", "--mailerpassword"):         # == "--mailerpassword":
            mailerpassword = arg
        elif opt in ("-r", "--receivers"):              #Array of the receiver emails
            receivers = arg
        elif opt in ("-s", "--sender"):                 #Sender email
            sender = arg
        elif opt in ("-c", "--commands"):                #On down, comma separated commands
            commands = arg
        elif opt in ("-U", "--urlcommand"):             #Run on down command only if url is the same as this urlcommand
            urlcommand = arg
        elif opt in ("-e", "--env"):                    #Environment of the this server, the source of the notification email
            env = arg
        elif opt in ("-H", "--help"):                   #On down command
           help()
           #sys.exit()
           return
        else:
            #print('webmonitor.py: invalid option')
            #logging.warning('webmonitor.py: parameter errors')
            help()
            sys.exit(2)


    if receivers:
        # remove space from receivers
        receivers = receivers.replace(" ", "")
        #print("receivers="+receivers)
        # receivers is comma separated string of receiver, convert to list
        global RECEIVERS
        RECEIVERS = list(receivers.split(","))

    if sender:
        global SENDER
        SENDER = sender

    if mailerhost:
        global MAILER_HOST
        MAILER_HOST = mailerhost

    if mailerport:
        global MAILER_PORT
        MAILER_PORT = mailerport

    if maileruser:
       global MAILER_USERNAME
       MAILER_USERNAME = maileruser

    if mailerpassword:
       global MAILER_PASSWORD
       MAILER_PASSWORD = mailerpassword

    if commands:
        global ON_DOWN_COMMANDS
        ON_DOWN_COMMANDS = commands

    if urlcommand:
        global URL_COMMAND
        URL_COMMAND = urlcommand

    if env:
        global ENV_NAME
        ENV_NAME = env

    print('urls=' + urls + ', mailerhost=' + mailerhost + ', maileruser=' + maileruser + ', mailerpassword=' + mailerpassword)
    #logging.info('urls=' + urls + ', mailerhost=' + mailerhost + ', maileruser=' + maileruser + ', mailerpassword=' + mailerpassword)

    if urls == '':
        print('Nothing to do: urls are not provided')
        #logging.warning('Nothing to do: urls are not provided')
        return

    #if mailerhost == '':
    #    print('Nothing to do: mailerhost is not provided')
    #    #logging.warning('Nothing to do: mailerhost is not provided')
    #    return

    if sender == '':
        print('Nothing to do: sender is not provided')
        #logging.warning('Nothing to do: sender is not provided')
        return

    if receivers == '':
        print('Nothing to do: receivers are not provided')
        #logging.warning('Nothing to do: receivers are not provided')
        return

    ##### Check sites #####
    #remove space from urls
    urls = urls.replace(" " ,"")
    #print("Get status for urls="+urls)
    #logging.info("Get status for urls="+urls)
    #urls is comma separated string of urls, convert to list
    listUrls = list(urls.split(","))

    #map
    #listBool = [True]*len(listUrls) #True - enable email if site is up
    #listBool = [False]*len(listUrls) #False - disable email if site is up
    #statusResultMap = map(get_site_status, listUrls, listBool)

    #runCommand('whoami') #testing runCommand

    statusResultList = []
    for url in listUrls:
        statusResult = get_site_status(url,False)
        #print(datetime.now().strftime('%Y-%B-%d %H:%M:%S'),url,"status=",statusResult)
        statusResultList.append(statusResult)
        if statusResult == 'down' and isLocalServer(url):
            restartServer(url)
            #check url again and send email if server is up
            time.sleep(3)
            get_site_status(url,True)

    #print(list(statusResultMap))
    #print(statusResultList)

if __name__ == '__main__':
    #python webmonitor.py -l "http://view.med.cornell.edu, http://view-test.med.cornell.edu"
    # -h "smtp.med.cornell.edu" -u "" -p "" -s "oli2002@med.cornell.edu" -r "oli2002@med.cornell.edu"
    # -c 'sudo systemctl restart postgresql-14, sudo systemctl restart httpd.service'
    # -U http://view-test.med.cornell.edu
    # -e TestServer
    main(sys.argv[1:])


#To run as apache, check if apache has permission: grep apache /etc/passwd
#If result is something like '/sbin/nologin' then cron won't work
#Test apache: sudo -u apache python /srv/order-lab-tenantapptest/webmonitor/webmonitor.py
#python /srv/order-lab-tenantapptest/webmonitor/webmonitor.py -l 'https://view-test.med.cornell.edu' -h smtp.med.cornell.edu --sender oli2002@med.cornell.edu -r oli2002@med.cornell.edu -c 'sudo systemctl restart postgresql-17, sudo systemctl restart haproxy, sudo systemctl restart php-fpm' --urlcommand 'https://view.online' -e TestServer2 >> /srv/order-lab-tenantapptest/orderflex/var/log/webmonitor.log
#python /srv/order-lab-tenantapptest/webmonitor/webmonitor.py -l 'https://view.online' -h smtp.med.cornell.edu --sender oli2002@med.cornell.edu -r oli2002@med.cornell.edu -c 'sudo systemctl restart postgresql-17, sudo systemctl restart haproxy, sudo systemctl restart php-fpm' --urlcommand 'https://view.online' -e TestServer2 >> /srv/order-lab-tenantapptest/orderflex/var/log/webmonitor.log
#view.online: python /srv/order-lab-tenantapptest/webmonitor/webmonitor.py -l 'https://view.online' -h smtp.gmail.com --sender view.online.administrator@pathologysystems.org -p 'mypassword' -r oli2002@med.cornell.edu -c 'sudo systemctl restart postgresql-17, sudo systemctl restart haproxy, sudo systemctl restart php-fpm' --urlcommand 'https://view.online' -e PublicTestServer >> /srv/order-lab-tenantapptest/orderflex/var/log/webmonitor.log

