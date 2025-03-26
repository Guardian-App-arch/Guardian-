# Guardian-
Guardian is an AI powered  solution solving app that use sensors to gather information from the environment and processes it and uses that informati to save individuals in potential danger by sending an emergency alert to a registered contact 

# Guardian Project Setup Instructions

This guide will walk you through setting up and running the Guardian project.

## Prerequisites

Before you begin, ensure you have the following software installed:

1.  **WampServer:**
    * Download and install WampServer from [http://www.wampserver.com/en/](http://www.wampserver.com/en/).
    * Ensure that your PHP version is 8.2 or higher. You can check the PHP version in WampServer's system tray icon menu.
2.  **Composer:**
    * Download and install Composer from [https://getcomposer.org/](https://getcomposer.org/). This is a dependency manager for PHP.
3.  **Visual Studio Code (VS Code):**
    * Download and install VS Code from [https://code.visualstudio.com/](https://code.visualstudio.com/). This is a code editor.

## Installation and Setup

Follow these steps to set up the Guardian project:

1.  **Clone the Git Repository:**
    * Open your preferred terminal or Git client.
    * Navigate to the directory where you want to store the project.
    * Clone the repository using the following command (replace `<repository_url>` with the actual URL of your Git repository):
        ```bash
        git clone <repository_url> guardian
        ```
2.  **Move the Project to WampServer's `www` Directory:**
    * Navigate to the cloned `guardian` folder.
    * Copy or move the entire `guardian` folder to the `C:\wamp64\www\` directory (for Windows users).
3.  **Enable the FFI Extension in PHP:**
    * Open WampServer.
    * Click on the WampServer system tray icon.
    * Go to "PHP" -> "PHP extensions".
    * Find the "php\_ffi" extension and click on it to enable it. If it does not appear in the php extensions list, you will have to manually edit the php.ini file.
    * Alternatively, you can manually edit the `php.ini` file:
        * Navigate to your PHP installation directory (e.g., `C:\wamp64\bin\php\php8.2.x\`). Replace `php8.2.x` with your actual PHP version.
        * Open the `php.ini` file in a text editor (like VS Code or Notepad).
        * Search for `extension=ffi`.
        * Remove the semicolon (`;`) at the beginning of the line.
        * Save the `php.ini` file.
        * Restart all services in wampserver.
4.  **Install the `whisper.php` Library:**
    * Open VS Code and open the `guardian` folder.
    * Open the integrated terminal in VS Code (View -> Terminal).
    * Run the following Composer command to install the required library:
        ```bash
        composer require codewithkyrian/whisper.php
        ```
        * If composer is not recognized, ensure it is added to the system's path.
5.  **Run the Project:**
    * Open your web browser (e.g., Chrome, Firefox).
    * Type `localhost/guardian/` in the address bar and press Enter.
    * The Guardian project interface should be displayed.
6.  **View Server Responses:**
    * To view the responses from the server, open the browser's developer tools.
    * In Chrome, press `Ctrl + Shift + I` (or `Cmd + Option + I` on macOS).
    * Navigate to the "Console" tab in the developer tools.

## Troubleshooting

* If you encounter errors related to missing PHP extensions, ensure that WampServer is using the correct PHP version and that the extensions are enabled.
* If you encounter composer errors, ensure composer is correctly installed, and the command line can find it.
* If the webpage is blank, check the apache error logs, and php error logs in the wamp server directory.
* Ensure that wampserver services are running.
