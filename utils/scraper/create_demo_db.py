import time
from web_automation import WebAutomation
from init import Init
from users import Users
from vacreq import VacReq
from trp import Trp
from calllog import CallLog
from fellapp import FellApp
from resapp import ResApp

#run demo db generation only if value is True
#if run successfully then set value flag to False so it does not run again second time
def runDemos(automation, demoIds, attempts, max_attempts):
    # Sections
    if 'init' in demoIds and demoIds['init']:
        try:
            init = Init(automation)
            init.initialize()
            init.run_site_settngs()
            init.run_deploy()
            time.sleep(3)
            demoIds['init'] = False
            print("init done!")
        except Exception as e:
            print("init failed:", e)
            attempts['init'] += 1
            return demoIds

    if 'users' in demoIds and demoIds['users']:
        try:
            users = Users(automation)
            users.create_user()
            time.sleep(3)
            demoIds['users'] = False
            print("users done!")
        except Exception as e:
            print("users failed:", e)
            attempts['users'] += 1
            return demoIds

    if 'vacreq' in demoIds and demoIds['vacreq']:
        try:
            vacreq = VacReq(automation)
            vacreq.create_group()
            vacreq.create_vacreqs()
            time.sleep(3)
            demoIds['vacreq'] = False
            print("vacreq done!")
        except Exception as e:
            print("vacreq failed:", e)
            attempts['vacreq'] += 1

    if 'trp' in demoIds and demoIds['trp']:
        try:
            trp = Trp(automation)
            trp.create_projects()
            time.sleep(3)
            demoIds['trp'] = False
            print("trp done!")
        except Exception as e:
            print("trp failed:", e)
            attempts['trp'] += 1

    if 'callog' in demoIds and demoIds['callog']:
        try:
            callog = CallLog(automation)
            callog.create_calllogs()
            time.sleep(3)
            demoIds['callog'] = False
            print("callog done!")
        except Exception as e:
            print("callog failed:", e)
            attempts['callog'] += 1

    if 'fellapp' in demoIds and demoIds['fellapp']:
        try:
            fellapp = FellApp(automation)
            fellapp.configs()
            fellapp.create_fellapps()
            time.sleep(3)
            demoIds['fellapp'] = False
            print("fellapp done!")
        except Exception as e:
            print("fellapp failed:", e)
            attempts['fellapp'] += 1

    if 'resapp' in demoIds and demoIds['resapp']:
        try:
            resapp = ResApp(automation)
            resapp.configs()
            resapp.create_resapps()
            time.sleep(3)
            demoIds['resapp'] = False
            print("resapp done!")
        except Exception as e:
            print("resapp failed:", e)
            attempts['resapp'] += 1

    # Disable retries for sections exceeding max attempts
    for key in demoIds.keys():
        if attempts[key] >= max_attempts:
            demoIds[key] = False

    return demoIds


def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"

    automation = WebAutomation()
    automation.login_to_site(url, username_text, password_text)
    #print("EOF testing")
    #exit()

    # Add demo IDs to retry in case of failure
    demoIds = {
        'init': True,
        'users': True,
        'vacreq': True,
        'trp': True,
        'callog': True,
        'fellapp': True,
        'resapp': True
    }

    # Track the number of attempts
    attempts = {key: 0 for key in demoIds.keys()}
    max_attempts = 2  # Set maximum retries per section

    print("Start demos")

    # Keep running demos until all sections are successful or exceed max attempts
    while any(demoIds.values()):
        demoIds = runDemos(automation, demoIds, attempts, max_attempts)

    print("All demos done!")
    automation.quit_driver()


# Execute the main function
if __name__ == "__main__":
    main()