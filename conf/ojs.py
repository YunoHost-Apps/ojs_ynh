import argparse
from playwright.sync_api import sync_playwright

parser = argparse.ArgumentParser(description="Automate OJS installation.")
parser.add_argument("--install-language", required=True, help="Installation language (e.g., en)")
parser.add_argument("--admin-username", required=True, help="Admin username")
parser.add_argument("--admin-password", required=True, help="Admin password")
parser.add_argument("--admin-email", required=True, help="Admin email")
parser.add_argument("--locale", required=True, help="Locale (e.g., en_US)")
parser.add_argument("--time-zone", required=True, help="Time zone (e.g., UTC)")
parser.add_argument("--files-dir", required=True, help="Files directory (e.g., /var/ojs/data)")
parser.add_argument("--database-driver", required=True, help="Database driver (e.g., mysqli)")
parser.add_argument("--database-host", required=True, help="Database host (e.g., localhost)")
parser.add_argument("--database-username", required=True, help="Database username")
parser.add_argument("--database-password", required=True, help="Database password")
parser.add_argument("--database-name", required=True, help="Database name")
parser.add_argument("--oai-repository-id", required=True, help="OAI Repository ID (e.g., domain.com)")
parser.add_argument("--url", required=True, help="URL (e.g., domain.com/path)")
args = parser.parse_args()

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    context = browser.new_context(ignore_https_errors=True)
    page = context.new_page()

    base_url = f"https://{args.url}/"
    print(f"Navigating to base URL: {base_url}")
    page.goto(base_url)

    print("Waiting to be redirected to the installation page...")
    page.wait_for_url("**/install", timeout=60000)

    print("Waiting for the installation form to load...")
    page.wait_for_selector('select[name="installLanguage"]', state="visible", timeout=360000)

    """
    # Debug begin
    print("Current URL after redirection:", page.url)
    
    form_elements = page.query_selector_all("form input, form select, form textarea")
    print(f"Found {len(form_elements)} form elements:")
    
    for el in form_elements:
        tag = el.evaluate("e => e.tagName.toLowerCase()")
        name = el.get_attribute("name")
        type_attr = el.get_attribute("type")
        id_attr = el.get_attribute("id")
        options = []
    
        if tag == "select":
            options = el.query_selector_all("option")
            option_values = [opt.get_attribute("value") for opt in options]
            print(f"  <select name='{name}' id='{id_attr}'> Options: {option_values}")
        else:
            print(f"  <{tag} name='{name}' id='{id_attr}' type='{type_attr}'>")

    # Debug end
    """
    
    print("Selecting the installation language...")
    page.select_option('select[name="installLanguage"]', value=args.install_language)

    print("Filling out admin details...")
    page.fill('input[name="adminUsername"]', args.admin_username)
    page.fill('input[name="adminPassword"]', args.admin_password)
    page.fill('input[name="adminPassword2"]', args.admin_password)
    page.fill('input[name="adminEmail"]', args.admin_email)

    print("Selecting the locale...")
    page.select_option('select[name="locale"]', value=args.locale)

    print("Selecting the time zone...")
    try:
        page.select_option('select[name="timeZone"]', value=args.time_zone)
        print(f"Selected time zone: {args.time_zone}")
    except Exception as e:
        print(f"Error selecting time zone: {e}")
        print("Falling back to UTC...")
        page.select_option('select[name="timeZone"]', value="UTC")

    print("Filling out the files directory...")
    page.fill('input[name="filesDir"]', args.files_dir)

    print("Selecting the database driver...")
    page.select_option('select[name="databaseDriver"]', value=args.database_driver)

    print("Filling out database details...")
    page.fill('input[name="databaseHost"]', args.database_host)
    page.fill('input[name="databaseUsername"]', args.database_username)
    page.fill('input[name="databasePassword"]', args.database_password)
    page.fill('input[name="databaseName"]', args.database_name)

    print("Filling out the OAI Repository ID...")
    page.fill('input[name="oaiRepositoryId"]', args.oai_repository_id)

    print("Submitting the form...")
    try:
        page.wait_for_selector('button[name="submitFormButton"]', state="visible", timeout=360000)
        with page.expect_navigation(timeout=60000):
            page.click('button[name="submitFormButton"]' , timeout=360000)
        print("Form submitted successfully.")
    except Exception as e:
        print(f"Error submitting the form: {e}")

    print("Waiting for the installation to complete...")
    page.wait_for_timeout(60000)

    with open("result.html", "w", encoding="utf-8") as f:
        f.write(page.content())

    print("Installation complete. Closing the browser...")
    browser.close()

print("Script finished.")
