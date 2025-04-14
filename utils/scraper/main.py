import time
from web_automation import WebAutomation
from users import Users
from vacreq import VacReq
from trp import Trp
from calllog import CallLog
from fellapp import FellApp
from resapp import ResApp

def runAllDemos( automation, demoIds ):
    # Create user
    if demoIds['users']:
        try:
            users = Users(automation)
            users.create_user()
            time.sleep(3)
            demoIds['users'] = False
        except Exception as e:
            print("users failed:", e)

    # Create Vacation Requests
    if demoIds['vacreq']:
        try:
            vacreq = VacReq(automation)
            vacreq.create_group()
            vacreq.create_vacreqs()
            demoIds['vacreq'] = False
            time.sleep(3)
        except Exception as e:
            print("vacreq failed:", e)

    if demoIds['trp']:
        try:
            trp = Trp(automation)
            trp.create_projects()
            time.sleep(3)
            demoIds['trp'] = False
        except Exception as e:
            print("trp failed:", e)

    if demoIds['callog']:
        try:
            callog = CallLog(automation)
            callog.create_calllogs()
            time.sleep(3)
            demoIds['callog'] = False
        except Exception as e:
            print("callog failed:", e)

    if demoIds['fellapp']:
        try:
            fellapp = FellApp(automation)
            fellapp.configs()
            fellapp.create_fellapps()
            time.sleep(3)
            demoIds['fellapp'] = False
        except Exception as e:
            print("fellapp failed:", e)

    if demoIds['resapp']:
        try:
            resapp = ResApp(automation)
            resapp.configs()
            resapp.create_resapps()
            time.sleep(3)
            demoIds['resapp'] = False
        except Exception as e:
            print("resapp failed:", e)

    return demoIds

def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"
    repeat_times = 2

    automation = WebAutomation()
    #driver = automation.initialize_driver()
    #driver = automation.get_driver()
    # You can now call methods like:
    automation.login_to_site(url, username_text, password_text)
    # automation.select_option("element_id", "select_classname", "option_text")
    # automation.click_button("button_class_name")

    #Add demo id to repeat in case of failure
    demoIds = {
        'users': True,
        'vacreq': True,
        'trp': True,
        'callog': True,
        'fellapp': True,
        'resapp': True
    }

    for key in demoIds:
        print(key, 'corresponds to', demoIds[key])
        if demoIds[key]:
            demoIds = runAllDemos(automation,demoIds)
            break

    automation.quit_driver()

# Execute the main function
if __name__ == "__main__":
    main()