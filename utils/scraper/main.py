import time
from web_automation import WebAutomation
from users import Users
from vacreq import VacReq
from trp import Trp
from calllog import CallLog
from fellapp import FellApp
from resapp import ResApp

def main():
    url = "https://view.online/c/demo-institution/demo-department/directory/login"
    username_text = "administrator"
    password_text = "1234567890_demo"

    automation = WebAutomation()
    #driver = automation.initialize_driver()
    #driver = automation.get_driver()
    # You can now call methods like:
    automation.login_to_site(url, username_text, password_text)
    # automation.select_option("element_id", "select_classname", "option_text")
    # automation.click_button("button_class_name")

    # Create user
    if 1:
        users = Users(automation)
        users.create_user()

    time.sleep(3)

    # Create Vacation Requests
    if 0:
        vacreq = VacReq(automation)
        vacreq.create_group()
        vacreq.create_vacreqs()
        time.sleep(3)

    if 0:
        trp = Trp(automation)
        trp.create_projects()
        time.sleep(3)

    if 1:
        callog = CallLog(automation)
        callog.create_calllogs()
        time.sleep(3)

    if 1:
        fellapp = FellApp(automation)
        fellapp.configs()
        fellapp.create_fellapps()
        time.sleep(3)

    if 1:
        resapp = ResApp(automation)
        resapp.configs()
        resapp.create_resapps()
        time.sleep(3)

    automation.quit_driver()

# Execute the main function
if __name__ == "__main__":
    main()