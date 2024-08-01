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

import os, sys, getopt, logging
import requests
import smtplib
from smtplib import SMTPException
from email.mime.text import MIMEText
import time
from datetime import datetime
import subprocess
from subprocess import PIPE
import urllib
#import yagmail

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



def get_site_status(url, sendSuccEmail=False):
    #print("url="+url)
    #logging.info("get_site_status: url="+url)

    # if COMMAND_COUNTER > 0:
    #     return 'Stop: COMMAND_COUNTER='+COMMAND_COUNTER

    status = ''
    response = None

    try:
        response = requests.get(url,verify=False)
    except:
        status = "Exception: requests.get("+url+")"
        #sendEmail(url, 'down')
        #return 'down: except'

    #print(response)
    #print("status_code="+str(response.status_code))
    #logging.info("status_code="+str(response.status_code))
    #print(response.headers)
    #print(response.content)

    if response != None:
        try:
            if getattr(response, 'status_code') == 200:
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

    print("get_site_status:","return=down","status=",status)
    return 'down'

def sendEmail(url, status):
    if status == "up":
        # site is up
        emailSubject = "Site " + url + " is accessible! (sent by webmonitor.py from "+ENV_NAME+")"
        emailBody = "Site " + url + " is UP!"
        send_email_alert(SENDER, RECEIVERS, emailSubject, emailBody)
        return True

    # site is down
    emailSubject = "Site " + url + " appears inaccessible!"
    emailBody = "Site " + url + " does not appear to be accessible. Please verify the site is operational! \n\n Sent by the independent script webmonitor.py from "+ENV_NAME
    send_email_alert(SENDER, RECEIVERS, emailSubject, emailBody)
    return False

def send_email_alert(fromEmail, toEmailList, emailSubject, emailBody):
    if MAILER_HOST == "":
        print("Error: unable to send email: MAILER_HOST is not provided")
        return False
        
    if MAILER_HOST == "smtp.gmail.com":
        send_gmail_email_alert(fromEmail, toEmailList, emailSubject, emailBody)
    else:
        send_local_email_alert(fromEmail, toEmailList, emailSubject, emailBody);

def send_local_email_alert(fromEmail, toEmailList, emailSubject, emailBody):
    print("send_local_email_alert:",fromEmail,toEmailList,emailSubject)
    if MAILER_HOST == "":
        print("Error: unable to send local email: MAILER_HOST is not provided")
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
        print("Successfully sent local email: ",emailSubject)
    except SMTPException:
        print("Error: unable to send email: ",emailSubject)
        #pass

#https://mailtrap.io/blog/python-send-email-gmail/
def send_gmail_email_alert(fromEmail, toEmailList, emailSubject, emailBody):
    print("send_gmail_email_alert:",fromEmail,toEmailList,emailSubject)
    if MAILER_HOST == "":
        print("Error: unable to send gmail email: MAILER_HOST is not provided")
        return False
    
    emailBody = emailBody + "\n\n" + datetime.now().strftime('%Y-%B-%d %H:%M:%S')
    msg = MIMEText(emailBody)
    msg['Subject'] = emailSubject
    msg['From'] = fromEmail
    msg['To'] = ', '.join(toEmailList)

    try:
        smtpObj = smtplib.SMTP_SSL('smtp.gmail.com', 465)
    except SMTPException:
        print("Error: unable to send gmail email: ",emailSubject) 
    else:
        with smtpObj:
            smtpObj.login(MAILER_USERNAME, MAILER_PASSWORD)
            smtpObj.sendmail(fromEmail, toEmailList, msg.as_string())
            print("Successfully sent gmail email: ",emailSubject)

def restartServer(url):
    if ON_DOWN_COMMANDS != "":
        #ON_DOWN_COMMANDS is a string or comma seperated string of commands
        commands = ON_DOWN_COMMANDS.split(",")
        outputs = []
        for command in commands:
            #try to run command
            output = runCommand(command.strip())
            outputs.append(output)

        # send email
        #send_email_alert(SENDER, RECEIVERS, "restartServer Test email", "restartServer Test email")
        emailSubject = "Run on down commands in " + ENV_NAME
        emailBody = "Trying to run on down commands for " + url + " in " + ENV_NAME + ":\n" + ON_DOWN_COMMANDS
        #emailBody = emailBody + "." + "\n\n Output=" + str(outputs)
        print("restartServer: emailSubject=" + emailSubject+", emailBody=" + emailBody)
        #send_email_alert(SENDER, RECEIVERS, "restartServer emailSubject", "restartServer emailBody")
        send_email_alert(SENDER, RECEIVERS, emailSubject, emailBody)

        #check url again
        #time.sleep(10)
        #get_site_status(url,True)

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
    print("isLocalServer: ",URL_COMMAND,url)
    if URL_COMMAND == url:
        return True
    return False

def main(argv):

    #print("\n### webmonitor.py "+datetime.now().strftime('%Y-%B-%d %H:%M:%S')+"###")
    #logging.basicConfig(filename='checksites.log',level=logging.INFO)
    #logging.info('main start')

    #response = requests.get('https://view-test.med.cornell.edu/',verify=False)
    #print(response)
    #sys.exit(2)

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

    #print('urls=' + urls + ', mailerhost=' + mailerhost + ', maileruser=' + maileruser + ', mailerpassword=' + mailerpassword)
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
    #restartServer("test url") #testing restartServer
    
    #Testing email
    #send_email_alert(SENDER, RECEIVERS, "Test email subject", "Test email body")
    #return

    statusResultList = []
    for url in listUrls:
        statusResult = get_site_status(url,False)
        #print(datetime.now().strftime('%Y-%B-%d %H:%M:%S'),url,"status=",statusResult)
        statusResultList.append(statusResult)
        #send_email_alert(SENDER, RECEIVERS, "1 Test email", "1 Test email: status="+statusResult)
        #if 1: # statusResult == 'down' and isLocalServer(url):
        #print(datetime.now().strftime('%Y-%B-%d %H:%M:%S'),": Before restartServer "+url)
        #send_email_alert(SENDER, RECEIVERS, "2 Test email subject", "2 Test email: body")
        if statusResult == 'down' and isLocalServer(url):
            #send_email_alert(SENDER, RECEIVERS, "2 Test email", "2 Test email: status=["+statusResult+"]")
            restartServer(url) #testing
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


