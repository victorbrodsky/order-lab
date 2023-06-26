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
import re


DIR = ""
FINDSTR = ""

def process_files( dir, startstr, endstr ):
    output = []

    if dir == '':
        res = "Subject directory name is empty"
        output.append(res)
        print(res)
        return output

    if startstr == '':
        res = "String to find and process is empty"
        output.append(res)
        print(res)
        return output

    dir = dir.strip()
    startstr = startstr.strip()
    endstr = endstr.strip()

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
    print("\n")

    for filepath in files:
        if ".php" in filepath:
            #print("filepath=", filepath)
            file = open(filepath, mode='r', encoding='utf8')
            content = file.read()
            #fileObject = glob.glob(file)
            #content = fileObject.read()
            if startstr in content:
                #print(startstr + "exists in ", filepath)
                process_single_file(filepath,startstr,endstr)

            file.close()
            #return #testing

        #1) find something like getRepository('AppUserdirectoryBundle:EventObjectTypeList')


    return output

def process_single_file( filepath, startstr, endstr ):
    with open(filepath, mode='r', encoding='utf8') as file:
        # read a list of lines into data
        data = file.readlines()

    namespaceline = None
    addLines = []

    for l_no, line in enumerate(data):
        # print('string found in a file', filepath)
        # print('Line Number:', l_no)
        # print('startstr in:', line)

        # get line with 'namespace App\DeidentifierBundle\Controller;'
        if 'namespace App' in line:
            namespaceline = l_no

        #https://stackoverflow.com/questions/4719438/editing-specific-line-in-text-file-in-python
        linemodified, bundle, classname = process_line(l_no, line, filepath, startstr, endstr)
        if linemodified != None:
            data[l_no] = linemodified
            print('Replaced: l_no=', l_no, " in " + filepath)
            print("Replaced line=",linemodified,"\n")

            #Now make sure class exists in the file's header "use class..."
            if [bundle,classname] not in addLines:
                addLines.append([bundle,classname])

    # Now make sure class exists in the file's header "use class..."
    if 1:
        print("\n\n Adding 'use ...'")
        file = open(filepath, mode='r', encoding='utf8')
        content = file.read()
        print("### addLines:", addLines, " for " + filepath)
        for bundle, classname in addLines:
            print("bundle=" + bundle + ", classname=" + classname)
            #AppOrderformBundle:Accession => use App\OrderformBundle\Entity\Accession;
            bundlefoldername = find_between(bundle+":", "App", ":")
            bundlefoldername_path = "../../orderflex/src/App/"+bundlefoldername
            bundlefiles = getListOfFiles(bundlefoldername_path)
            print("!!! "+bundlefoldername_path+": bundlefiles=", len(list(bundlefiles)))
            foundcount = 0
            #foundclass = None
            classpath = None
            for bundlefile in bundlefiles:
                #print("bundlefile="+bundlefile)
                if os.path.basename(bundlefile) == classname+".php":
                    print("Found to add use: "+bundlefile)
                    #foundclass = bundlefile
                    classpath = bundlefile
                    foundcount = foundcount + 1

            if foundcount == 1:
               #../../orderflex/src/App/UserdirectoryBundle\Entity\EventTypeList.php =>
               # use App\OrderformBundle\Entity\EventTypeList;
                bundledirname = os.path.dirname(classpath)
                subfolder = os.path.basename(bundledirname)
                useline = r'use App\{}\{}\{};'.format(bundlefoldername,subfolder,classname)  # +  r"\" + bundlefoldername + r"\" + subfolder + r"\" + classname
                if useline not in content:
                    useline = useline + " //process.py script: replaced namespace by ::class: added use line for classname=" + classname
                    #print("useline=" + useline)
                    print("Added use: " + useline + " in ", filepath)
                    #add after namespace App\DeidentifierBundle\Controller;
                    #print(data)
                    #print("namespaceline=",namespaceline)
                    data[namespaceline] = data[namespaceline] + "\n\n\n" + useline


    # and write everything back
    if 1:
        with open(filepath, 'w', encoding='utf8') as file:
            file.writelines(data)

    return

def process_line( l_no, origline, filepath, startstr, endstr ):
    line = origline.lstrip()
    # print("\n")
    if startstr in line and endstr in line:
        if line[:2] != '//':
            if line.count(startstr) == 1 and line.count(endstr) == 1:
                # print('string found in a file', filepath)
                # print('Line Number:', l_no)
                # print('startstr in:', line)
                # result = re.search(startstr+'(.*)'+endstr, line)
                result = find_between(line, startstr, endstr)  # AppOrderformBundle:AccessionType
                #print('result=', result)
                # AppOrderformBundle:AccessionType
                x = result.split(":")
                bundle = x[0]
                classname = x[1]

                #get single ' or double quote " from line and result
                #line: $accessionTypes = $em->getRepository('AppOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );
                #subline (startstr + result + endstr): ->getRepository(' + result + ')->
                #result: AccessionType::class
                #get: '
                quote = "'"
                firstChar = endstr[0]
                lastChar = startstr[-1]
                if firstChar == lastChar:
                    quote = lastChar
                    #print("quote=" + quote)
                else:
                    print("Skipped in filepath=" + filepath + "\n" + "line=" + line + "\n" + "Skipped: quote char is not defined" + "\n")

                # User::class
                searchstr = quote + result + quote
                replacedstr = classname + "::class"
                if searchstr in origline:
                    print('Replaced: bundle=', bundle, ', classname=', classname,"=> searchstr=" + searchstr + " replacedstr=" + replacedstr)
                    linemodified = origline.replace(searchstr, replacedstr)
                    linemodified = "    //process.py script: replaced namespace by ::class: [" + searchstr + "] by [" + replacedstr + "]" + "\n" + linemodified
                    return linemodified, bundle, classname
                else:
                    print("Skipped in filepath=" + filepath + "\n" + "line=" + line + "\n" + "Skipped: searchstr [" + searchstr + "] is not in the line" + "\n")
            else:
                print("Skipped in filepath=" + filepath + "\n" + "line=" + line + "\n" + "Skipped: start/end strings occurred more than 1 time" + "\n")
        else:
            # print(filepath + "\n" + "line="+line+"\n"+"Skipped: line commented out")
            pass
    return None, None, None

#NOT USED
def process_single_file_orig(filepath, startstr, endstr):
    count = 0
    with open(filepath, mode='r', encoding='utf8') as fp:
        for l_no, origline in enumerate(fp):
            line = origline.lstrip()
            #print("\n")
            if startstr in line and endstr in line:
                if line[:2] != '//':
                    if line.count(startstr) == 1 and line.count(endstr) == 1:
                        #print('string found in a file', filepath)
                        #print('Line Number:', l_no)
                        #print('startstr in:', line)
                        #result = re.search(startstr+'(.*)'+endstr, line)
                        result = find_between(line,startstr,endstr) #AppOrderformBundle:AccessionType
                        print('result=', result)
                        #AppOrderformBundle:AccessionType
                        x = result.split(":")
                        bundle = x[0]
                        classname = x[1]
                        #User::class
                        searchstr = "'"+result+"'"
                        replacedstr = classname+"::class"
                        print('bundle=', bundle, ', classname=', classname, "=> searchstr="+searchstr+" replacedstr="+replacedstr+"\n")
                        count = count + 1
                    else:
                        print("Skipped in filepath="+filepath + "\n" + "line=" + line + "\n" + "Skipped: start/end strings occurred more than 1 time"+"\n")
                        #pass
                else:
                    #print(filepath + "\n" + "line="+line+"\n"+"Skipped: line commented out")
                    pass

    print("count=",count," filepath="+filepath)

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

def find_between( s, first, last ):
    try:
        start = s.index( first ) + len( first )
        end = s.index( last, start )
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
        "Example: python process.py --dir DeidentifierBundle --startstr \"getRepository('\" --endstr \"')\" \n" \
        "\n" \
        "-d, --dir              subject directory to process files\n" \
        "-s, --startstr         start string to replace\n" \
        "-e, --endstr           end string to replace\n" \
        "-H, --help             this help"
    )

def main(argv):
    print("\n### process.py "+"###")

    dir = ''
    startstr = ''
    endstr = ''

    try:
        opts, args = getopt.getopt(
            argv,
            "d:s:e:h",
            ["dir=", "startstr=", "endstr=", "help"]
        )
    except getopt.GetoptError:
        print('Parameters error')
        sys.exit(2)

    for opt, arg in opts:
        #print('opt=' + opt + ", arg="+arg)
        if opt in ("-d", "--dir"):
            dir = arg
        elif opt in ("-s", "--startstr"):
            startstr = arg
        elif opt in ("-e", "--endstr"):
            endstr = arg
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

    if startstr:
        global FINDSTR
        FINDSTR = startstr

    print('dir=' + dir + ', startstr=' + startstr + ', endstr=' + endstr)

    if dir == '':
        print('Nothing to do: subject directory are not provided')
        return

    if startstr == '':
        print('Nothing to do: startstr is not provided')
        return

    if endstr == '':
        print('Nothing to do: endstr is not provided')
        return

    runCommand('whoami') #testing runCommand

    output = process_files(dir,startstr,endstr)

    print(output)

if __name__ == '__main__':
    #python fellapp.py --dir DeidentifierBundle --startstr "->getRepository('" --endstr "')->"
    #C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\replace-test\DeidentifierBundle
    #python process.py -d ../../orderflex/src/App/TestBundle -s "->getRepository('" -e "')->" > res.log
    main(sys.argv[1:])