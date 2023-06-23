#!/usr/bin/env python
# Created by Oleg Ivanov

#Modify files
#1) find something like getRepository('AppUserdirectoryBundle:EventObjectTypeList')
#2) get string 'AppUserdirectoryBundle' and string 'EventObjectTypeList'
#3) replace 'AppUserdirectoryBundle:EventObjectTypeList' by 'EventObjectTypeList::class'
#4) use string 'AppUserdirectoryBundle' to get the location of the file EventObjectTypeList
#5) construct the namespace according to the file location: 'use App\UserdirectoryBundle\Entity;'
#6) add this 'use ...' to the beginning of the file if it does not exist in the file

import os, sys, getopt
from subprocess import check_output
import glob, shutil

DIR = ""
FINDSTR = ""

def process_files( dir, findstr ):
    output = []

    if dir == '':
        res = "Subject directory name is empty"
        output.append(res)
        print(res)
        return output

    if findstr == '':
        res = "String to find and process is empty"
        output.append(res)
        print(res)
        return output

    dir = dir.strip()
    findstr = findstr.strip()

    if not os.path.exists(dir):
        res = "Folder does not exist: " + dir
        output.append(res)
        print(res)
        return output

    dir_path = os.path.abspath(dir)

    #1) find something like getRepository('AppUserdirectoryBundle:EventObjectTypeList')


    # 2) Create new folder, for example “MyFellowshipApplication”
    #Final destination path is currentfolder/scripts/dest_dir_name
    #dest_dir = "scripts/"+dest_dir_name
    #print("dest_dir="+dest_dir)



    #test
    # projectid = "1qOC476n4UCg2lfWzAUSbdg7uRGX3reTCHK9PcNDBDogqGpYw969kmBSO"
    # command = "clasp open "+projectid
    # res = runCommand(command.strip())
    # #output.append(res)
    # return output



    # 5) Push all files from local folder to Google Drive: $ clasp push
    #Switch to dest_dir
    #os.chdir(dest_dir)





    # 7) Using that version number, you can deploy instances of your project: $ clasp deploy -V 1
    command = clasppath + " deploy -V 1"
    res = runCommand(command.strip())
    
    #output.append(res)


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

def help():
    print(
        "Usage: python fellapp.py [OPTIONS]\n" \
        "Example: python fellapp.py --dir MyFellApp --title MyFellApp --clasp C:/Users/ch3/AppData/Roaming/npm/clasp \n" \
        "\n" \
        "-d, --dir              folder name where to install the local copies of the Google scripts. New folder will be created to ./script/\n" \
        "-t, --title            title of a new Google script\n" \
        "-c, --clasp            path to clasp\n" \
        "-s, --source           path to the original source script\n" \
        " \n" \
        "-e, --env              environment info (optional)\n" \
        "-H, --help             this help"
    )

def main(argv):
    print("\n### process.py "+"###")

    dir = ''            # -d
    findstr = ''        # -t

    try:
        opts, args = getopt.getopt(
            argv,
            "d:f:h",
            ["dir=", "findstr=", "help"]
        )
    except getopt.GetoptError:
        print('Parameters error')
        sys.exit(2)

    for opt, arg in opts:
        print('opt=' + opt + ", arg="+arg)
        if opt in ("-d", "--dir"):
            dir = arg
        elif opt in ("-f", "--findstr"):
            title = arg
        elif opt in ("-h", "--help"):
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

    if findstr:
        global FINDSTR
        FINDSTR = findstr

    print('dir=' + dir + ', findstr=' + findstr)

    if dir == '':
        print('Nothing to do: subject directory are not provided')
        return

    if findstr == '':
        print('Nothing to do: string to find and process is not provided')
        return

    runCommand('whoami') #testing runCommand

    output = process_files(dir,findstr)

    print(output)

if __name__ == '__main__':
    #C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\utils\google-integration\venv\Scripts\python.exe fellapp.py --dir "C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\utils\google-integration\scripts\MyScriptFellApp" --title “MyScript” --clasp C:/Users/ch3/AppData/Roaming/npm/clasp
    #--source C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\src\App\FellAppBundle\Util\GoogleForm\FellowshipRecLetters\script
    main(sys.argv[1:])