#!/usr/bin/env python
# Created by Oleg Ivanov


#Ref: 

# sample usage: python filebackup.py -s path/source/foldername -d path/destination/foldername
# -i
# -s

import os, sys, getopt, logging
#import smtplib
#from smtplib import SMTPException
#from email.mime.text import MIMEText
import time
from datetime import datetime
import subprocess
from subprocess import PIPE
import shutil

SOURCE_PATH = ""
DESTINATION_PATH = ""

COMMAND_COUNTER = 0

def help():
    print(
        "Usage: python filebackup.py [OPTION]...\n" \
        "\n" \
        "-s, --source           path to the source folder\n" \
        "-d, --dest             path to the destination folder\n" \
        "-H, --help             this help"
    )


#python filesbackup.py -s test -d myarchive
def start_backup(source, dest):
    print("source=",source,"dest=",dest)
    #logging.info("get_site_status: url="+url)

    #https://docs.python.org/3/library/shutil.html#shutil.make_archive
    #>> > from shutil import make_archive
    #>> > import os
    #>> > archive_name = os.path.expanduser(os.path.join('~', 'myarchive'))
    #>> > root_dir = os.path.expanduser(os.path.join('~', '.ssh'))
    #>> > make_archive(archive_name, 'gztar', root_dir)
    #'/Users/tarek/myarchive.tar.gz'
    #archive_name = os.path.expanduser(os.path.join('~', 'myarchive'))

    shutil.make_archive(dest, 'zip', source)

    return 'backup ok'

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

def main(argv):

    print("\n### webmonitor.py "+datetime.now().strftime('%Y-%B-%d %H:%M:%S')+"###")
    #logging.basicConfig(filename='checksites.log',level=logging.INFO)
    #logging.info('main start')

    source = ''           # -s
    dest = ''             # -d

    try:
        opts, args = getopt.getopt(
            argv,
            "s:d:h",
            ["source=", "dest=", "help"
             ]
        )
    except getopt.GetoptError:
        print('Parameters error')
        #logging.warning('Parameters error')
        #help()
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-s", "--source"):
            source = arg
            #print('webmonitor.py --urls=' + urls)
        elif opt in ("-d", "--dest"):
            dest = arg
        elif opt in ("-H", "--help"):
           help()
           #sys.exit()
           return
        else:
            #print('backupfiles.py: invalid option')
            #logging.warning('backupfiles.py: parameter errors')
            help()
            sys.exit(2)


    if source:
        #print("source="+source)
        global SOURCE_PATH
        SOURCE_PATH = source

    if dest:
        global DESTINATION_PATH
        DESTINATION_PATH = dest

    print('source=',source,', dest=',dest)
    #logging.info('urls=' + urls + ', mailerhost=' + mailerhost + ', maileruser=' + maileruser + ', mailerpassword=' + mailerpassword)

    if source == '':
        print('Nothing to do: source is not provided')
        #logging.warning('Nothing to do: source is not provided')
        return

    if dest == '':
        print('Nothing to do: destination is not provided')
        #logging.warning('Nothing to do: destination is not provided')
        return

    runCommand('whoami') #testing

    res = start_backup(source, dest)

    print(res)

if __name__ == '__main__':
    #python webmonitor.py -l "http://view.med.cornell.edu, http://view-test.med.cornell.edu"
    # -h "smtp.med.cornell.edu" -u "" -p "" -s "oli2002@med.cornell.edu" -r "oli2002@med.cornell.edu"
    # -c 'sudo systemctl restart postgresql-14, sudo systemctl restart httpd.service'
    # -U http://view-test.med.cornell.edu
    # -e TestServer
    main(sys.argv[1:])


