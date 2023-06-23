#!/usr/bin/env python
# Created by Oleg Ivanov

#Modify files
#1) find something like "->getRepository('"
#->getRepository('AppUserdirectoryBundle:EventObjectTypeList')->
#2) from tha line in 1 get the string 'AppUserdirectoryBundle' and string 'EventObjectTypeList'
#3) replace 'AppUserdirectoryBundle:EventObjectTypeList' by 'EventObjectTypeList::class'
#4) use string 'AppUserdirectoryBundle' to get the location of the file EventObjectTypeList
#5) construct the namespace according to the file location: 'use App\UserdirectoryBundle\Entity;'
#6) add this 'use ...' to the beginning of the file if it does not exist in the file

import os, sys, getopt
from subprocess import check_output
import glob, shutil
from os import walk


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
    print("dir_path=", dir_path)

    #0) get all files
    files = getListOfFiles(dir_path)
    print("files=", len(list(files)))

    for filepath in files:
        if ".php" in filepath:
            #print("filepath=", filepath)
            file = open(filepath, mode='r', encoding='utf8')
            content = file.read()
            #fileObject = glob.glob(file)
            #content = fileObject.read()
            if findstr in content:
                #print(findstr + "exists in ", filepath)
                process_single_file(filepath,file,findstr)

            file.close()

        #1) find something like getRepository('AppUserdirectoryBundle:EventObjectTypeList')


    return output

def process_single_file( filepath, file, findstr ):
    with open(filepath, mode='r', encoding='utf8') as fp:
        for l_no, line in enumerate(file):
            # search string
            if findstr in line:
                print('string found in a file', filepath)
                print('Line Number:', l_no)
                print('Line:', line)
                # don't look for next lines
                # break


    # lines = file.readlines()
    # print("lines",len(lines))
    # for line in lines:
    #     print(line)
    #     if line.find(findstr) != -1:
    #         print(findstr, 'string exists in file')

    # while True:
    #     line = file.readline()
    #     print("search for " + findstr)
    #     if findstr in line:
    #         print(findstr + "exists in " + line)
    #     if line == '':
    #         break


def getListOfFiles(dirName):
    # create a list of file and sub directories
    # names in the given directory
    listOfFile = os.listdir(dirName)
    allFiles = list()
    # Iterate over all the entries
    for entry in listOfFile:
        # Create full path
        fullPath = os.path.join(dirName, entry)
        # If entry is a directory then get the list of files in this directory
        if os.path.isdir(fullPath):
            allFiles = allFiles + getListOfFiles(fullPath)
        else:
            allFiles.append(fullPath)

    return allFiles

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
        "Usage: python process.py [OPTIONS]\n" \
        "Example: python process.py --dir DeidentifierBundle --findstr \"getRepository('\" \n" \
        "\n" \
        "-d, --dir              subject directory to process files\n" \
        "-f, --findstr          string to replace\n" \
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
        #print('opt=' + opt + ", arg="+arg)
        if opt in ("-d", "--dir"):
            dir = arg
        elif opt in ("-f", "--findstr"):
            findstr = arg
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
    #python fellapp.py --dir DeidentifierBundle --findstr "->getRepository('"
    main(sys.argv[1:])