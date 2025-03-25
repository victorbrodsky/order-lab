# import the required library
from selenium import webdriver

options = webdriver.ChromeOptions()
options.add_argument("--encoding=utf-8")
options.add_experimental_option("detach", True)
driver = webdriver.Chrome(
    options=options,
)

# initialize an instance of the chrome driver (browser)
#chrome_path = r"C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\drivers\chromedriver-win64\chromedriver.exe"
#driver = webdriver.Chrome(service=chrome_path)
driver = webdriver.Chrome()

# visit your target site
driver.get("https://www.scrapingcourse.com/ecommerce/")

# output the full-page HTML
print(driver.page_source)

# release the resources allocated by Selenium and shut down the browser
driver.quit()
