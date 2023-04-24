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
import glob, shutil
#import filecmp
#from pathlib import Path
import webbrowser
import re

DIR = ""
TITLE = ""
ENV_NAME = "Unknown"

def help():
    print(
        "Usage: python fellapp-gas-install.py [OPTIONS]\n" \
        "Example: python fellapp-gas-install.py --dir MyFellowshipApplication --title MyFellApp\n" \
        "\n" \
        "-d, --dir              folder name where to install the local copies of the Google scripts. New folder will be created to ./script/\n" \
        "-t, --title            title of a new Google script\n" \
        " \n" \
        "-e, --env              environment info as a string attached to the notification email\n" \
        "-H, --help             this help"
    )

def install_gas( dest_dir_name, title ):
    output = []

    if dest_dir_name == '':
        res = "Destination directory name is empty"
        output.append(res)
        print(res)
        return output

    if title == '':
        res = "Script title is empty"
        output.append(res)
        print(res)
        return output

    dest_dir_name = dest_dir_name.strip()
    title = title.strip()


    # 1) Go to this folder and login to your Google Account: $ clasp login
    command = "clasp login"
    res = runCommand(command.strip())
    output.append(res)

    # 2) Create new folder, for example “MyFellowshipApplication”
    #Final destination path is currentfolder/scripts/dest_dir_name
    dest_dir = "scripts/"+dest_dir_name
    if not os.path.exists(dest_dir):
        os.makedirs(dest_dir)
    else:
        res = "Folder already exists: " + dest_dir
        output.append(res)
        print(res)
        return output

    #test
    # projectid = "1qOC476n4UCg2lfWzAUSbdg7uRGX3reTCHK9PcNDBDogqGpYw969kmBSO"
    # command = "clasp open "+projectid
    # res = runCommand(command.strip())
    # #output.append(res)
    # return output

    # 3) Create a new Apps Script project: $ clasp create --title “MyFellApp” --type webapp
    dest_path = os.path.abspath(dest_dir)
    #print("dest_path="+dest_path)
    #return dest_path

    command = "clasp create --type webapp --title " + title + " --rootDir " + dest_path
    res = runCommand(command.strip())
    output.append(res)

    # 4) Copy all GAS files from C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\src\App\FellAppBundle\Util\GoogleForm\FellowshipApplication\script
    # to local folder "MyFellowshipApplication”, except .clasp.json

    #"C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/"
    source_dir = "../../"+"orderflex/src/App/FellAppBundle/Util/GoogleForm/FellowshipApplication/script/"
    print("source_dir="+source_dir)

    #Get absolute path
    source_path = os.path.abspath(source_dir)
    dest_path = os.path.abspath(dest_dir)
    print("source_path=", source_path)
    print("dest_path=", dest_path)

    #Prevent over write the original GAS files in the source directoy
    if source_path == dest_path:
        res = "Source and destination folders are the same. You can not overwrite the original GAS files in the source directory"
        print(res)
        output.append(res)
        return output

    #Copy all files except .clasp.json
    copyfiles(source_dir, dest_dir, "*.*")

    # 5) Push all files from local folder to Google Drive: $ clasp push
    #Switch to dest_dir
    os.chdir(dest_dir)

    print("push files to Google Drive")
    command = "clasp push -f"
    res = runCommand(command.strip())
    #print("push res=" + res)
    output.append(res)

    # 6) Create new version: $ clasp version
    # This command displays the newly created version number 1.
    command = "clasp version 'auto created Fellapp script'"
    res = runCommand(command.strip())
    #print("version="+res)
    output.append(res)

    # 7) Using that version number, you can deploy instances of your project: $ clasp deploy -V 1
    command = "clasp deploy -V 1"
    res = runCommand(command.strip())
    #- AKfycbyPTUG0fNRdb0QO-DdHB3KYG356b8GPr5YhxDEzHJxut8wI782U4e4u45w-VqFMqZJN @1.
    #deploymentId = str(res).replace("b'- ",'')
    #deploymentId = deploymentId.replace("@1.",'')
    #deploymentId = deploymentId.strip()
    #print("1 deploymentId=" + deploymentId)

    res = str(res)
    res = res.strip()
    print("res=" + res)
    deploymentId = find_between_r( res, "- ", "@1" )
    deploymentId = deploymentId.strip()
    print("2 deploymentId=" + deploymentId)
    output.append(res)

    # Open project script and set permission by running Code.gs
    #command = "clasp open --webapp" asks Open which deployement?
    command = "clasp open"
    #command = "clasp run Code.gs"
    res = runCommand(command.strip())
    output.append(res)
    #Login again, Click "Allow"
    #Go to "Deploy" -> "Manage Deployments" -> copy Web app URL -> Run this URL in private browser
    #TODO: wait and set permission

    # 8) Test deployment web url
    if False:
        #https://script.google.com/macros/s/AKfycbypzF0jiZHcUSEVK9TV7_ZrD2llrMNN9ZHGxHi6rZlWVG8PspVfl3UmzEFO1PvJfKZW2g/exec
        url = "https://script.google.com/macros/s/"+deploymentId+"/exec"
        print("URL="+url)
        webbrowser.open(url)  # Go to example.com
        output.append(res)

    return output

def copyfiles( source_dir, dest_dir, pattern ):
    files = glob.iglob(os.path.join(source_dir,pattern))
    # print("files=", len(list(files)))
    for file in files:
        # print(file)
        if os.path.isfile(file):
            print(file)
            shutil.copy2(file, dest_dir)

def find_between_r( s, first, last ):
    try:
        start = s.rindex( first ) + len( first )
        end = s.rindex( last, start )
        return s[start:end]
    except ValueError:
        return ""

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

    output = install_gas(dir,title)

    print(output)

if __name__ == '__main__':
    #python fellapp.py --dir "MyFellowshipApplication" --title “MyFellApp”
    main(sys.argv[1:])