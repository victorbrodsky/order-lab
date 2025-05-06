import time
from web_automation import WebAutomation
from init import Init
from users import Users
from vacreq import VacReq
from trp import Trp
from calllog import CallLog
from fellapp import FellApp
from resapp import ResApp
import getpass
import sys


#run demo db generation only if value is True
#if run successfully then set value flag to False so it does not run again second time
def run_demos(automation, demo_ids, attempts, max_attempts, mailer_user, mailer_password):
    # Sections
    if 'init' in demo_ids and demo_ids['init'] and attempts['init'] <= max_attempts:
        print("init attempt=",attempts['init'])
        try:
            init = Init(automation)
            init.initialize()
            init.run_site_settngs()
            init.init_mailer(mailer_user,mailer_password)
            init.run_deploy()
            time.sleep(3)
            demo_ids['init'] = False
            print("init done!")
        except Exception as e:
            print("init failed:", e)
            attempts['init'] += 1
            return demo_ids

    if 'users' in demo_ids and demo_ids['users'] and attempts['users'] <= max_attempts:
        print("users attempt=", attempts['users'])
        try:
            users = Users(automation)
            users.create_user()
            time.sleep(3)
            demo_ids['users'] = False
            print("users done!")
        except Exception as e:
            print("users failed:", e)
            attempts['users'] += 1
            return demo_ids

    if 'vacreq' in demo_ids and demo_ids['vacreq'] and attempts['vacreq'] <= max_attempts:
        print("vacreq attempt=", attempts['vacreq'])
        try:
            vacreq = VacReq(automation)
            vacreq.create_group()
            vacreq.add_user_to_group()
            #vacreq.create_vacreqs()
            time.sleep(3)
            demo_ids['vacreq'] = False
            print("vacreq done!")
        except Exception as e:
            print("vacreq failed:", e)
            attempts['vacreq'] += 1

    if 'trp' in demo_ids and demo_ids['trp'] and attempts['trp'] <= max_attempts:
        print("trp attempt=", attempts['trp'])
        try:
            trp = Trp(automation)
            trp.create_projects()
            time.sleep(3)
            demo_ids['trp'] = False
            print("trp done!")
        except Exception as e:
            print("trp failed:", e)
            attempts['trp'] += 1

    if 'callog' in demo_ids and demo_ids['callog'] and attempts['callog'] <= max_attempts:
        print("callog attempt=", attempts['callog'])
        try:
            callog = CallLog(automation)
            callog.create_calllogs()
            time.sleep(3)
            demo_ids['callog'] = False
            print("callog done!")
        except Exception as e:
            print("callog failed:", e)
            attempts['callog'] += 1

    if 'fellapp' in demo_ids and demo_ids['fellapp'] and attempts['fellapp'] <= max_attempts:
        print("fellapp attempt=", attempts['fellapp'])
        try:
            fellapp = FellApp(automation)
            fellapp.configs()
            fellapp.create_fellapps()
            time.sleep(3)
            demo_ids['fellapp'] = False
            print("fellapp done!")
        except Exception as e:
            print("fellapp failed:", e)
            attempts['fellapp'] += 1

    if 'resapp' in demo_ids and demo_ids['resapp'] and attempts['resapp'] <= max_attempts:
        print("resapp attempt=", attempts['resapp'])
        try:
            resapp = ResApp(automation)
            resapp.configs()
            resapp.create_resapps()
            time.sleep(3)
            demo_ids['resapp'] = False
            print("resapp done!")
        except Exception as e:
            print("resapp failed:", e)
            attempts['resapp'] += 1

    # Disable retries for sections exceeding max attempts
    for key in demo_ids.keys():
        if attempts[key] >= max_attempts:
            demo_ids[key] = False

    return demo_ids


def main(mailer_user, mailer_password):
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"

    if mailer_user is None:
        mailer_user = ""

    if mailer_password is None:
        mailer_password = ""

    automation = WebAutomation(run_by_symfony_command=True)
    automation.login_to_site(url, username_text, password_text)
    #print("EOF testing")
    #exit()

    # Add demo IDs to retry in case of failure
    demo_ids = {
        'init': True,
        'users': True,
        'vacreq': True,
        #'trp': True,
        #'callog': True,
        #'fellapp': True,
        #'resapp': True
    }

    # Track the number of attempts
    attempts = {key: 0 for key in demo_ids.keys()}
    max_attempts = 2  # Set maximum retries per section

    print("Start demos")

    # Keep running demos until all sections are successful or exceed max attempts
    while any(demo_ids.values()):
        demo_ids = run_demos(automation, demo_ids, attempts, max_attempts, mailer_user, mailer_password)

    print("All demos done!")
    automation.quit_driver()


# Execute the main function
if __name__ == "__main1__":
    #password = getpass.getpass("Enter your password: ")  # Secure input
    user = None
    password = None
    main(user,password)

if __name__ == "__main__":
    if "--mailerpassword" in sys.argv:
        index = sys.argv.index("--mailerpassword") + 1
        if index < len(sys.argv):
            mailer_password = sys.argv[index]
            main(mailer_password)
        else:
            print("Error: No password provided after --mailerpassword")
    else:
        print("Error: --mailerpassword not found in arguments")

    main()

import sys



if __name__ == "__main__":
    args = sys.argv

    if "--maileruser" in args and "--emailpassword" in args:
        mailer_index = args.index("--maileruser") + 1
        password_index = args.index("--emailpassword") + 1

        if mailer_index < len(args) and password_index < len(args):
            mailer_user = args[mailer_index]
            email_password = args[password_index]
            main(mailer_user, email_password)
        else:
            print("Error: Missing values for --maileruser or --emailpassword")
    else:
        print("Error: --maileruser or --emailpassword not found in arguments")

    main()