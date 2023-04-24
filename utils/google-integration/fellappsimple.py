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

import os, sys, getopt
#from subprocess import check_output
#import glob, shutil
import fellapp


def help():
    print(
        "Create FellApp and RecLet scripts by only providing the title as a prefix. "
        "The resulting local copies of the scripts titleFellApp and titleRecLet will be located in ./script/titleFellApp and ./script/titleRecLet"
        "Usage: python fellappsimple.py [OPTIONS]\n" \
        "Example: python fellapp.py --title MyFellApp --clasp C:/Users/ch3/AppData/Roaming/npm/clasp \n" \
        "\n" \
        "-t, --title            title of a new Google script\n" \
        "-c, --clasp            path to clasp\n" \
        " \n" \
        "-e, --env              environment info (optional)\n" \
        "-H, --help             this help"
    )

def main(argv):
    #print("\n### fellapp.py "+datetime.now().strftime('%Y-%B-%d %H:%M:%S')+"###")
    #logging.basicConfig(filename='checksites.log',level=logging.INFO)
    #logging.info('main start')

    title = ''          # -t
    clasp = ''          # -c clasp path
    env = ''            # -e

    try:
        opts, args = getopt.getopt(
            argv,
            "t:c:e:h",
            ["title=", "clasp=",
             "env=", "help"
            ]
        )
    except getopt.GetoptError:
        print('Parameters error')
        sys.exit(2)

    for opt, arg in opts:
        print('opt=' + opt + ", arg="+arg)
        if opt in ("-t", "--title"):
            title = arg
        elif opt in ("-c", "--clasp"):
            clasp = arg
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

    print('title=' + title + ', clasp=' + clasp)

    if title == '':
        print('Nothing to do: title is not provided')
        #logging.warning('Nothing to do: mailerhost is not provided')
        return

    if clasp == '':
        clasp = 'clasp'
        print('clasp path is not provided')
        #return

    fellapp.runCommand('whoami') #testing runCommand

    #currentDir = "/"
    #currentPath = os.path.abspath(currentDir)
    currentPath = os.path.dirname(os.path.realpath(__file__))
    print("currentPath="+currentPath)

    titleFellApp = title + "FellApp"
    titleRecLet = title + "RecLet"

    destDirFellApp = "scripts/" + titleFellApp
    destDirRecLet = "scripts/" + titleRecLet

    sourceDirFellApp = "../../" + "orderflex/src/App/FellAppBundle/Util/GoogleForm/FellowshipApplication/script/"
    sourceDirRecLet = "../../" + "orderflex/src/App/FellAppBundle/Util/GoogleForm/FellowshipRecLetters/script/"

    print("destDirFellApp=" + destDirFellApp)
    outputFellApp = fellapp.install_gas( sourceDirFellApp, destDirFellApp, titleFellApp, clasp )
    print("outputFellApp=",outputFellApp)

    print("\n\n\n")
    os.chdir(currentPath)

    print("destDirRecLet="+destDirRecLet)
    outputRecLet = fellapp.install_gas( sourceDirRecLet, destDirRecLet, titleRecLet, clasp )
    print("outputRecLet=",outputRecLet)

if __name__ == '__main__':
    #C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\utils\google-integration\venv\Scripts\python.exe fellapp.py --title “MyScript” --clasp C:/Users/ch3/AppData/Roaming/npm/clasp
    main(sys.argv[1:])