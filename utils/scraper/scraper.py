# import the required library
from selenium import webdriver
from selenium.webdriver.common.by import By

#set PYTHONIOENCODING=utf-8
#export PYTHONIOENCODING=utf-8

options = webdriver.ChromeOptions()
#options.add_argument("--encoding=utf-8")
options.add_experimental_option("detach", True)
driver = webdriver.Chrome(
    options=options,
)

# initialize an instance of the chrome driver (browser)
#chrome_path = r"C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\drivers\chromedriver-win64\chromedriver.exe"
#driver = webdriver.Chrome(service=chrome_path)
driver = webdriver.Chrome()

driver.page_source.encode('utf-8')

# visit your target site
url = "https://view.online/c/demo-institution/demo-department/time-away-request/login";
#driver.get("https://www.scrapingcourse.com/ecommerce/")
driver.get(url)

# output the full-page HTML
#print(driver.page_source)

username = driver.find_element(By.ID, "display-username")
password = driver.find_element(By.ID, "password")
username.send_keys("administrator")
password.send_keys("1234567890_demo")

usertype = driver.find_element(By.ID, "usernametypeid_show")
usertype.send_keys("local-user")

usertype = driver.find_element(By.ID, "s2id_usernametypeid_show")
usertype.send_keys("Local User")

# release the resources allocated by Selenium and shut down the browser
driver.quit()
