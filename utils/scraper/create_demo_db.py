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
import os
import multiprocessing
import subprocess



#run demo db generation only if value is True
#if run successfully then set value flag to False so it does not run again second time
def run_demos(demo_ids, attempts, max_attempts, mailer_user, mailer_password, run_by_symfony_command):
    #run_by_symfony_command = True
    #run_by_symfony_command = False
    # Sections
    if 'init' in demo_ids and demo_ids['init'] and attempts['init'] <= max_attempts:
        print("init attempt=",attempts['init'])
        try:
            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            init = Init(automation)
            init.initialize()
            init.run_site_settngs()
            init.init_other_settings()
            init.remove_crons()
            init.init_mailer(mailer_user,mailer_password)
            init.run_deploy()
            time.sleep(3)
            automation.quit_driver()
            #del automation
            #del init
            demo_ids['init'] = False
            print("init done!")
        except Exception as e:
            print("init failed:", e)
            attempts['init'] += 1
            return demo_ids
        finally:
            del automation
            del init

    #Stop all following demos if init failed
    if demo_ids['init'] and attempts['init'] >= max_attempts:
        print("Init failed. Exit all demos.")
        sys.exit()

    if 'users' in demo_ids and demo_ids['users'] and attempts['users'] <= max_attempts:
        print("users attempt=", attempts['users'])
        try:
            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            users = Users(automation)
            users.create_user()
            users.check_users()
            time.sleep(3)
            automation.quit_driver()
            #del automation
            #del users
            demo_ids['users'] = False
            print("users done!")
        except Exception as e:
            print("users failed:", e)
            attempts['users'] += 1
            return demo_ids
        finally:
            del automation
            del users

    # Stop all following demos if users failed
    if demo_ids['users'] and attempts['users'] >= max_attempts:
        print("Users failed. Exit all demos.")
        sys.exit()

    ##### Check if system is login able ####
    automation = WebAutomation(run_by_symfony_command)
    if automation.check_login_page():
        #OK
        print("check_login_page passed")
        del automation
    else:
        #Error
        print("check_login_page failed")
        del automation
        sys.exit()

    # automation.login_to_site()
    # driver = automation.get_driver()
    #
    # url = "https://view.online/c/demo-institution/demo-department/directory/logout"
    # driver.get(url)
    # time.sleep(3)
    #
    # url = "https://view.online/c/demo-institution/demo-department/directory/login"
    # driver.get(url)
    # time.sleep(3)
    #
    # try:
    #     element = driver.find_element(By.ID, "display-username")
    #     print("Element display-username found!")
    # except NoSuchElementException:
    #     print("display-username not found.")
    #     driver.save_screenshot("login_page_error.png")
    #     del automation
    #     sys.exit()

    if 'vacreq' in demo_ids and demo_ids['vacreq'] and attempts['vacreq'] <= max_attempts:
        print("vacreq attempt=", attempts['vacreq'])
        try:
            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            vacreq = VacReq(automation)
            vacreq.create_group()
            automation.quit_driver()
            del automation
            del vacreq

            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            vacreq = VacReq(automation)
            vacreq.add_user_to_group()
            vacreq.add_submitter_to_group()
            vacreq.create_vacreqs()
            time.sleep(3)
            automation.quit_driver()
            #del automation
            #del vacreq
            demo_ids['vacreq'] = False
            print("vacreq done!")
        except Exception as e:
            print("vacreq failed:", e)
            attempts['vacreq'] += 1
        finally:
            del automation
            del vacreq

    if 'trp' in demo_ids and demo_ids['trp'] and attempts['trp'] <= max_attempts:
        print("trp attempt=", attempts['trp'])
        try:
            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            trp = Trp(automation)
            trp.create_projects()
            time.sleep(3)
            automation.quit_driver()
            del automation

            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            trp.set_automation(automation)
            trp.create_work_requests()
            automation.quit_driver()
            #del automation

            #del trp
            demo_ids['trp'] = False
            print("trp done!")
        except Exception as e:
            print("trp failed:", e)
            attempts['trp'] += 1
        finally:
            del automation
            del trp

    if 'callog' in demo_ids and demo_ids['callog'] and attempts['callog'] <= max_attempts:
        print("callog attempt=", attempts['callog'])
        try:
            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            callog = CallLog(automation)
            callog.create_calllogs()
            time.sleep(3)
            automation.quit_driver()
            #del automation
            #del callog
            demo_ids['callog'] = False
            print("callog done!")
        except Exception as e:
            print("callog failed:", e)
            attempts['callog'] += 1
        finally:
            del automation
            del callog

    if 'fellapp' in demo_ids and demo_ids['fellapp'] and attempts['fellapp'] <= max_attempts:
        print("fellapp attempt=", attempts['fellapp'])
        try:
            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            fellapp = FellApp(automation)
            fellapp.configs()
            fellapp.set_site_settings()
            del automation
            del fellapp

            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            fellapp = FellApp(automation)
            fellapp.create_fellapps()
            time.sleep(3)
            automation.quit_driver()
            #del automation
            #del fellapp
            demo_ids['fellapp'] = False
            print("fellapp done!")
        except Exception as e:
            print("fellapp failed:", e)
            attempts['fellapp'] += 1
        finally:
            del automation
            del fellapp

    if 'resapp' in demo_ids and demo_ids['resapp'] and attempts['resapp'] <= max_attempts:
        print("resapp attempt=", attempts['resapp'])
        try:
            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            resapp = ResApp(automation)
            resapp.configs()
            del automation
            del resapp

            automation = WebAutomation(run_by_symfony_command)
            automation.login_to_site()
            resapp = ResApp(automation)
            resapp.create_resapps()
            time.sleep(3)
            automation.quit_driver()
            #del automation
            #del resapp
            demo_ids['resapp'] = False
            print("resapp done!")
        except Exception as e:
            print("resapp failed:", e)
            attempts['resapp'] += 1
        finally:
            del automation
            del resapp

    # Disable retries for sections exceeding max attempts
    for key in demo_ids.keys():
        if attempts[key] >= max_attempts:
            demo_ids[key] = False

    return demo_ids


def main(mailer_user, mailer_password):
    print("script directory:", os.getcwd())  # This will show the directory where your script is running

    #subprocess.run(["/usr/bin/bash", "/srv/order-lab-tenantappdemo/orderflex/deploy.sh"], check=True)
    #print("main: after deploy.sh")
    #sys.exit()

    run_by_symfony_command = True
    # run_by_symfony_command = False

    # if run_by_symfony_command is True:
        # write output to a file
        #It's better to use: /usr/bin/php /srv/order-lab-tenantappdemo/orderflex/bin/console cron:demo-db-reset --env=prod >> /srv/order-lab-tenantappdemo/orderflex/scraper.log 2>&1
        # #Option 1
        # if 0:
        #     log_file_path = "/srv/order-lab-tenantappdemo/orderflex/scraper.log"
        #     if not os.path.exists(log_file_path):
        #         log_file_path = os.getcwd() + "/scraper.log"
        #     log_file = open(log_file_path, "w")
        #     sys.stdout = log_file

    print("script directory:",os.getcwd())  # This will show the directory where your script is running

    if mailer_user is None:
        mailer_user = "maileruser"

    if mailer_password is None:
        mailer_password = "mailerpassword"

    print("mailer_user=", mailer_user, "mailer_password=", mailer_password)

    # Add demo IDs to retry in case of failure. True flag means that this demo has to be run
    demo_ids = {
        'init': True,
        'users': True,
        'vacreq': True,
        'trp': True,
        'callog': True,
        'fellapp': True,
        'resapp': True
    }

    # Track the number of attempts
    attempts = {key: 0 for key in demo_ids.keys()}
    max_attempts = 2  # Set maximum retries per section

    print("Start demos")

    # Keep running demos until all sections are successful or exceed max attempts
    while any(demo_ids.values()):
        demo_ids = run_demos(demo_ids, attempts, max_attempts, mailer_user, mailer_password, run_by_symfony_command)

    #clean cach: 'bash deploy.sh'
    #automation = WebAutomation(run_by_symfony_command)
    #automation.login_to_site()
    #init = Init(automation)
    #init.run_deploy()
    #/srv/order-lab-tenantappdemo/orderflex
    #os.chdir("/srv/order-lab-tenantappdemo/orderflex/")
    #os.system("/usr/bin/bash /srv/order-lab-tenantappdemo/orderflex/deploy.sh")
    subprocess.run(["/usr/bin/bash", "/srv/order-lab-tenantappdemo/orderflex/deploy.sh"], check=True)
    print("main: after deploy.sh")

    print("All demos done!")
    #automation.quit_driver()

def get_arg_value(args, key):
    try:
        return args[args.index(key) + 1]
    except (ValueError, IndexError):
        return None

if __name__ == "__main__":
    args = sys.argv
    print("args=", args)

    mailer_user = get_arg_value(args, "--maileruser")
    mailer_password = get_arg_value(args, "--mailerpassword")

    if mailer_user and mailer_password:
        main(mailer_user, mailer_password)
    else:
        print("Error: Missing values for --maileruser or --mailerpassword")
        print("Proceed without mailer")
        main('maileruser', 'mailerpassword')


# if 0 and __name__ == "__main__":
#     args = sys.argv
#     print("args=",args)
#
#     if "--maileruser" in args and "--mailerpassword" in args:
#         mailer_index = args.index("--maileruser") + 1
#         password_index = args.index("--mailerpassword") + 1
#
#         if mailer_index < len(args) and password_index < len(args):
#             mailer_user = args[mailer_index]
#             mailer_password = args[password_index]
#             main(mailer_user, mailer_password)
#         else:
#             print("Error: Missing values for --maileruser or --mailerpassword")
#             print("Proceed without mailer")
#             main('maileruser', 'mailerpassword')
#     else:
#         print("Error: --maileruser or --mailerpassword not found in arguments")
#         print("Proceed without mailer")
#         main('maileruser','mailerpassword')

if 0 and __name__ == "__main__":
    args = sys.argv
    print("args=", args)

    processes = []

    if "--maileruser" in args and "--mailerpassword" in args:
        mailer_index = args.index("--maileruser") + 1
        password_index = args.index("--mailerpassword") + 1

        if mailer_index < len(args) and password_index < len(args):
            mailer_user = args[mailer_index]
            mailer_password = args[password_index]

            # Creating a process to run main function
            p = multiprocessing.Process(target=main, args=(mailer_user, mailer_password))
            processes.append(p)
            p.start()
        else:
            print("Error: Missing values for --maileruser or --mailerpassword")
    else:
        print("Error: --maileruser or --mailerpassword not found in arguments")

    print("Proceed without mailer")
    p2 = multiprocessing.Process(target=main, args=('maileruser', 'mailerpassword'))
    processes.append(p2)
    p2.start()

    # Waiting for all processes to complete
    for p in processes:
        p.join()
