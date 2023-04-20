#!/usr/bin/env python
# Created by Oleg Ivanov

#Install Google scripts for Fellowship Application and Recommendation Letters submission forms
#https://github.com/google/clasp
#https://developers.google.com/apps-script/guides/clasp
#1) Create new folder, for example “MyFellowshipApplication”
#2) Go to this folder and login to your Google Account: $ clasp login
#3) Create a new Apps Script project: $ clasp create --title “MyFellApp” --type webapp
#4) Copy all GAS files from C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\src\App\FellAppBundle\Util\GoogleForm\FellowshipApplication\script
# to local folder "MyFellowshipApplication”, except .clasp.json
#5) Push all files from local folder to Google Drive: $ clasp push
#6) Create new version: $ clasp version
# This command displays the newly created version number 1.
#7) Using that version number, you can deploy instances of your project: $ clasp deploy -V 1

# sample usage: python fellapp-gas-install.py --dir "MyFellowshipApplication" --title “MyFellApp”
# -d --dir: folder name "MyFellowshipApplication"
# -t --title: title of a new Google script
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
from subprocess import check_output


DIR = ""
TITLE = ""
ENV_NAME = "Unknown"

def help():
    print(
        "Usage: python fellapp-gas-install.py [OPTIONS]\n" \
        "Example: python fellapp-gas-install.py --dir 'MyFellowshipApplication' --title 'MyFellApp'\n" \
        "\n" \
        "-d, --dir              folder name where to install the local copies of the Google scripts\n" \
        "-t, --title            title of a new Google script\n" \
        " \n" \
        "-e, --env              environment info as a string attached to the notification email\n" \
        "-H, --help             this help"
    )

def install( dir, title ):
    command = "clasp login"
    #command = "clasp --version"
    output = runCommand(command.strip())

    command = "clasp create --type webapp --title " + title + " --rootDir " + dir
    output = runCommand(command.strip())

    return output


def runCommand(command):
    print("run: " + command)
    #output = subprocess.run([command], stdout=PIPE, stderr=PIPE, shell=True)
    output = check_output(command, shell=True)
    print(output)
    return output

def main(argv):

    print("\n### webmonitor.py "+datetime.now().strftime('%Y-%B-%d %H:%M:%S')+"###")
    #logging.basicConfig(filename='checksites.log',level=logging.INFO)
    #logging.info('main start')

    dir = ''            # -d
    title = ''          # -t
    env = ''            # -e

    try:
        opts, args = getopt.getopt(
            argv,
            "d:t:e:h",
            ["dir=", "title=",
             "env=", "help"
            ]
        )
    except getopt.GetoptError:
        print('Parameters error')
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-d", "--dir"):
            dir = arg
            #print('webmonitor.py --urls=' + urls)
        elif opt in ("-t", "--title"):
            title = arg
        elif opt in ("-e", "--env"):                    #Environment of the this server, the source of the notification email
            env = arg
        elif opt in ("-h", "--help"):                   #On down command
           help()
           #sys.exit()
           return
        else:
            #print('webmonitor.py: invalid option')
            #logging.warning('webmonitor.py: parameter errors')
            help()
            sys.exit(2)

    if dir:
        global DIR
        DIR = dir

    if title:
        global TITLE
        TITLE = title

    if env:
        global ENV_NAME
        ENV_NAME = env

    print('dir=' + dir + ', title=' + title)
    #logging.info('urls=' + urls + ', mailerhost=' + mailerhost + ', maileruser=' + maileruser + ', mailerpassword=' + mailerpassword)

    if dir == '':
        print('Nothing to do: dir are not provided')
        #logging.warning('Nothing to do: urls are not provided')
        return

    if title == '':
        print('Nothing to do: title is not provided')
        #logging.warning('Nothing to do: mailerhost is not provided')
        return

    runCommand('whoami') #testing runCommand

    res = install(dir,title)

    print(res)

if __name__ == '__main__':
    #python fellapp.py --dir "C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\FellApp\MyFellowshipApplication" --title “MyFellApp”
    main(sys.argv[1:])