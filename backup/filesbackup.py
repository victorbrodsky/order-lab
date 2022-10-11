#!/usr/bin/env python
# Created by Oleg Ivanov


#Ref: 

# sample usage: python filebackup.py -s path/source/dirname -d path/destination/dirname
# -i
# -s

import os, sys, getopt, logging
import smtplib
from smtplib import SMTPException
from email.mime.text import MIMEText
import time
from datetime import datetime
import subprocess
from subprocess import PIPE
import shutil

#SOURCE_PATH = ""
#DESTINATION_PATH = ""
MAILER_HOST = ""
#MAILER_PORT = ""
MAILER_USERNAME = ""
#MAILER_PASSWORD = ""

COMMAND_COUNTER = 0

def help():
    print(
        "Usage: python filebackup.py [OPTION]...\n" \
        "\n" \
        "-s, --source           path to the source directory\n" \
        "-b, --basedir          directory to archive\n" \
        "-d, --dest             path to the destination directory\n" \
        "-H, --help             this help"
    )


#python filesbackup.py -s test -d myarchive
#python filesbackup.py -s 'C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\backup' -d myarchive -b test -h "smtp.med.cornell.edu" -f oli2002@med.cornell.edu -r oli2002@med.cornell.edu
def start_backup(source, dest, basedir):
    print("source=",source,", dest=",dest,", basedir=",basedir)
    #logging.info("get_site_status: url="+url)

    #https://docs.python.org/3/library/shutil.html#shutil.make_archive
    #>> > from shutil import make_archive
    #>> > import os
    #>> > archive_name = os.path.expanduser(os.path.join('~', 'myarchive'))
    #>> > root_dir = os.path.expanduser(os.path.join('~', '.ssh'))
    #>> > make_archive(archive_name, 'gztar', root_dir)
    #'/Users/tarek/myarchive.tar.gz'
    #archive_name = os.path.expanduser(os.path.join('~', 'myarchive'))

    archivefile = ''
    try:
        if basedir != None:
            archivefile = shutil.make_archive(dest, 'zip', source, base_dir=basedir)
        else:
            archivefile = shutil.make_archive(dest, 'zip', source)
    except Exception as inst:
        print("Error archiving: ",inst)
        return None

    #print('archivefile=',archivefile)

    return archivefile

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
    time.sleep(10)

def send_email_alert(mailerhost, fromEmail, toEmailList, emailSubject, emailBody):
    emailBody = emailBody + "\n\n" + datetime.now().strftime('%Y-%B-%d %H:%M:%S')
    msg = MIMEText(emailBody)
    msg['Subject'] = emailSubject
    msg['From'] = fromEmail
    msg['To'] = ', '.join(toEmailList)

    MAILER_PORT = ''

    try:
        #print("MAILER_HOST=" + MAILER_HOST+", MAILER_PORT="+MAILER_PORT)
        smtpObj = smtplib.SMTP(mailerhost, MAILER_PORT)
        #if MAILER_USERNAME != "" and MAILER_PASSWORD != "":
        #    smtpObj.starttls()
        #    smtpObj.login(MAILER_USERNAME, MAILER_PASSWORD)
        smtpObj.sendmail(fromEmail, toEmailList, msg.as_string())
        print("Successfully sent email")
    except SMTPException:
        print("Error: unable to send email")
        #pass

def main(argv):

    print("\n### filesbackup.py "+datetime.now().strftime('%Y-%B-%d %H:%M:%S')+"###")
    #logging.basicConfig(filename='checksites.log',level=logging.INFO)
    #logging.info('main start')

    source = ''           # -s
    basedir = ''          # -b
    dest = ''             # -d
    mailerhost = ''
    maileruser = ''
    receivers = ''  # -r
    fromEmail = ''  # -s

    try:
        opts, args = getopt.getopt(
            argv,
            "s:b:d:h:u:r:f:H",
            ["source=", "basedir=", "dest=", "mailerhost=", "maileruser=", "receivers=", "fromemail=", "help"]
        )
    except getopt.GetoptError:
        print('Parameters error')
        #logging.warning('Parameters error')
        #help()
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-s", "--source"):
            source = arg
            #print('filesbackup.py --urls=' + urls)
        elif opt in ("-b", "--basedir"):
            basedir = arg
        elif opt in ("-d", "--dest"):
            dest = arg
        elif opt in ("-h", "--mailerhost"):             # == "--mailerhost":
            mailerhost = arg
        elif opt in ("-u", "--maileruser"):             # == "--maileruser":
            maileruser = arg
        elif opt in ("-r", "--receivers"):              #Array of the receiver emails
            receivers = arg
        elif opt in ("-f", "--fromemail"):                 #Sender email
            fromEmail = arg
        elif opt in ("-H", "--help"):
           help()
           #sys.exit()
           return
        else:
            #print('backupfiles.py: invalid option')
            #logging.warning('backupfiles.py: parameter errors')
            help()
            sys.exit(2)


    # if source:
    #     #print("source="+source)
    #     global SOURCE_PATH
    #     SOURCE_PATH = source
    #
    # if basedir:
    #     global DESTINATION_PATH
    #     DESTINATION_PATH = dest
    #
    # if dest:
    #     global DESTINATION_PATH
    #     DESTINATION_PATH = dest

    # if mailerhost:
    #     global MAILER_HOST
    #     MAILER_HOST = mailerhost
    #
    # if maileruser:
    #    global MAILER_USERNAME
    #    MAILER_USERNAME = maileruser

    print('source=',source,', basedir=',basedir, 'dest=',dest, ", mailerhost=",mailerhost,", receivers=",receivers,", fromEmail=",fromEmail)
    #logging.info('urls=' + urls + ', mailerhost=' + mailerhost + ', maileruser=' + maileruser + ', mailerpassword=' + mailerpassword)

    if source == '':
        print('Nothing to do: source is not provided')
        #logging.warning('Nothing to do: source is not provided')
        return

    if basedir == '':
        print('Nothing to do: basedir is not provided')
        return

    if dest == '':
        print('Nothing to do: destination is not provided')
        #logging.warning('Nothing to do: destination is not provided')
        return

    toEmailList = ''
    if receivers:
        # receivers is comma separated string of receiver, convert to list
        receivers = receivers.replace(" ", "")
        # receivers is comma separated string of receiver, convert to list
        toEmailList = list(receivers.split(","))

    runCommand('whoami') #testing

    archivefile = start_backup(source, dest, basedir)

    if archivefile == None:
        if mailerhost:
            emailSubject = "Error archiving folder " + basedir
            emailBody = "Error creating archive '" + dest + "' for folder " + basedir + " in " + source
            send_email_alert(mailerhost, fromEmail, toEmailList, emailSubject, emailBody)
        else:
            print("Mailer parameters are not provided: Error email has not been sent")

    print("Result Archivefile=",archivefile)

if __name__ == '__main__':
    #python filesbackup.py -s test -d myarchive

    main(sys.argv[1:])


