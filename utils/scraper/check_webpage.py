import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.common.exceptions import NoSuchElementException
import tempfile
import time

#sudo -u apache google-chrome --headless --disable-gpu --no-sandbox
#mkdir: cannot create directory ‘/usr/share/httpd/.local’: Permission denied
#sudo -u apache env SE_CACHE_PATH=/srv/order-lab-tenantapptest/orderflex/var/cache google-chrome --headless --disable-gpu --no-sandbox

class Checker:
    def __init__(self):
        #self.url = url
        #os.environ['SE_CACHE_PATH'] = '/srv/order-lab-tenantapptest/orderflex/var/cache'
        #os.environ['XDG_CACHE_HOME'] = '/srv/order-lab-tenantapptest/orderflex/var/cache'
        #user_data_dir = tempfile.mkdtemp(prefix="chrome-profile-", dir="/var/www/.cache")
        # driver = webdriver.Chrome()
        options = webdriver.ChromeOptions()
        options.add_argument("--no-sandbox")  # working in command. Disable the Chrome sandbox, which is a security feature that isolates browser processes
        options.add_argument("--disable-dev-shm-usage")  # working in command. Prevent Chrome from using shared memory
        # if self.run_by_symfony_command is True:
        options.add_argument("--headless")  # working in command. Run a browser without a graphical user interface

        # self.user_data_dir = tempfile.mkdtemp(prefix="chrome-profile-", dir="/srv/order-lab-tenantapptest/orderflex/var/cache")
        # import shutil
        # shutil.rmtree(self.user_data_dir, ignore_errors=True)
        # self.user_data_dir = tempfile.mkdtemp(prefix="chrome-profile-",
        #                                       dir="/srv/order-lab-tenantapptest/orderflex/var/cache")
        # options.add_argument(f"--user-data-dir={self.user_data_dir}")

        #user_data_dir = tempfile.mkdtemp(prefix="chrome-profile-",dir="/srv/order-lab-tenantapptest/orderflex/var/cache")
        #options.add_argument(f"--user-data-dir={user_data_dir}")

        #profile_path = os.path.join(os.getcwd(), f"chrome_profiles/{time.strftime('%m_%d_%Y_%H_%M_%S')}")
        #if not os.path.isdir(profile_path):
        #    os.makedirs(profile_path)

        #options.add_argument(f"--user-data-dir={profile_path}")
        #options.add_argument(f"--profile-directory=Default")

        # Change cache folder for selenium to be accessible by apache, or run as root
        # os.environ['SE_CACHE_PATH'] = '/srv/order-lab-tenantapptest/orderflex/var/cache'
        # options.add_argument("--cache-path=/srv/order-lab-tenantapptest/orderflex/var/cache") #or SE_CACHE_PATH
        # options.add_argument("--profile=/srv/order-lab-tenantapptest/orderflex/var/cache")
        # options.add_argument("--user-data-dir=/usr/local/bin/order-lab-tenantappdemo/orderflex/var/log/")

        # options.add_experimental_option("detach", True)
        self.driver = webdriver.Chrome(options=options)

    # def __init__(self):
    #     # Set custom cache directory for selenium
    #     #cache_dir = '/var/www/.cache'
    #     cache_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'orderflex', 'var', 'cache'))
    #     print(f"cache_dir={cache_dir}")
    #     #cache_dir = '/srv/order-lab-tenantapptest/orderflex/var/'
    #     os.environ['XDG_CACHE_HOME'] = cache_dir
    #
    #     # Ensure the cache directory exists
    #     if not os.path.exists(cache_dir):
    #         print(f"create cache_dir={cache_dir}")
    #         try:
    #             os.makedirs(cache_dir, exist_ok=True)
    #             os.chown(cache_dir, os.getuid(), os.getgid())  # Optional: set ownership
    #         except PermissionError:
    #             print(f"Permission denied: cannot create {cache_dir}. Run script with proper privileges.")
    #     else:
    #         print(f"existed cache_dir={cache_dir}")

    # check if expected element exists on the web page
    # like: input type="hidden" id="heartbeatInput" name="status" value="alive"
    def check_element_on_webpage(self,url):
        # pass
        print("###check_element_on_webpage###")
        status = False

        # #driver = webdriver.Chrome()
        # options = webdriver.ChromeOptions()
        # options.add_argument("--no-sandbox")  # working in command. Disable the Chrome sandbox, which is a security feature that isolates browser processes
        # options.add_argument("--disable-dev-shm-usage")  # working in command. Prevent Chrome from using shared memory
        #
        # #if self.run_by_symfony_command is True:
        # options.add_argument("--headless")  # working in command. Run a browser without a graphical user interface
        #
        # user_data_dir = tempfile.mkdtemp(prefix="chrome-profile-", dir="/srv/order-lab-tenantapptest/orderflex/var/cache")
        # options.add_argument(f"--user-data-dir={user_data_dir}")
        #
        # #Change cache folder for selenium to be accessible by apache, or run as root
        # #os.environ['SE_CACHE_PATH'] = '/srv/order-lab-tenantapptest/orderflex/var/cache'
        # #options.add_argument("--cache-path=/srv/order-lab-tenantapptest/orderflex/var/cache") #or SE_CACHE_PATH
        # #options.add_argument("--profile=/srv/order-lab-tenantapptest/orderflex/var/cache")
        # #options.add_argument("--user-data-dir=/usr/local/bin/order-lab-tenantappdemo/orderflex/var/log/")
        #
        # #options.add_experimental_option("detach", True)
        # driver = webdriver.Chrome(options=options)

        print("###check_element_on_webpage: before driver.get(url) ###")

        # Navigate to the webpage
        self.driver.get(url)

        print("###check_element_on_webpage: after driver.get(url) ###")

        # Check if the element exists
        try:
            element = self.driver.find_element(By.ID, "heartbeatInput")
            print("###Element heartbeatInput exists.###")
            status = True
        except NoSuchElementException:
            print("###Element heartbeatInput does not exist.###")

        # Close the browser
        self.driver.quit()

        print(f"###check_element_on_webpage: return status={status} ###")

        return status


if __name__ == "__main__":
    checker = Checker()
    url = "https://view.online/c/wcm/pathology"
    #url = "https://view-test.med.cornell.edu"
    print("url=",url)
    checker.check_element_on_webpage(url)







