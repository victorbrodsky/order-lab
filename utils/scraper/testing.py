from init import Init
from web_automation import WebAutomation

if __name__ == "__main__":
    run_by_symfony_command = True
    automation = WebAutomation(run_by_symfony_command)
    automation.login_to_site()
    init = Init(automation)
    #init.run_deploy_command()
    init.run_deploy_command_new()

