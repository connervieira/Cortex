# Documentation

This document exlains how to install, setup, and use Cortex.


## Introduction

### Terminology

Cortex and Predator form a somewhat complex link, and it's important to understand a few terms before trying to install Cortex.

- The **instance** refers to the instance of Predator that is being controlled by Cortex.
- The **controller** refers to Cortex, controlling a Predator instance.

- The **interface directory** is a directory used by Predator to feed information that will be read by Cortex. Think of this directory as the bridge Predator uses to active share information as it operates.
- The **instance directory** is the main Predator directory, containing all of the scripts and support files used by the back-end.
- The **controller directory** is the main Cortex directory, containing all of the scripts and support files used by the front-end controller interface.

### Security

Cortex is primarily intended to be installed on a system dedicated to the usage of Predator. As such, the following instructions often involve granting permissions without regard for the security of other applications. If you plan to install Cortex on a system running multiple services, use caution when granting very relaxed permissions.

### Methodology

Cortex must be installed on the device hosting Predator, and it interacts with the instance in a few different ways.

- **Fetching** information is done using the interface directory, as defined previously. Predator places information regarding the current states into this directory for other programs to read.
- **Controlling** the instance is done directly, using a shell script. When the user commands Cortex to start or stop Predator, a shell script is executed accordingly.
- **Configuring** the instance is done by directly modifying its configuration file.


## Installing

### Dependencies

There are a few dependencies that need to be installed for Cortex to function.

1. Install Apache, or another web-server host.
    - Example: `sudo apt-get install apache2`
2. Install and enable PHP for your web-server.
    - Example: `sudo apt-get install php8.1; sudo a2enmod php8.1`
3. Restart your web-server host.
    - Example: `sudo apache2ctl restart`

### Installation

After the dependencies are installed, copy the Cortex directory from the source you received it from, to the root of your web-server directory.

For example: `cp ~/Downloads/Cortex /var/www/html/cortex`


## Set Up

### Permissions

For Cortex to function properly, Apache and PHP must be granted administrative rights. Without these, the controller won't be able to start and stop processes.

1. Open the sudo configuration file with the command `visudo`
2. Add the line `www-data ALL=(ALL) NOPASSWD: ALL`
3. Save the document and exit.
4. You should also make sure that the `cortex` directory is writable to PHP.


### Connecting

After the basic set-up process is complete, you should be able to view the Cortex interface in a web browser.

1. Open a web browser of your choice.
2. Enter the URL for your Cortex installation.
    - Example: `http://192.168.0.76/cortex/`
3. After the login page appears, enter the default password, `predator`.
4. Once you've logged in, you should see the main interface.

It should be noted that you're likely to see several errors at this point, given that Cortex hasn't been fully configured yet.


### Configuring

Once you've verified that Cortex is working as expected, you should configure it.

1. Click the "Settings" button on the main Cortex dashboard.
2. Click the "Controller Settings" button on the Settings page.
3. Adjust settings as necessary or desired.

The "Interface Settings" section contains settings relating to the graphical Cortex interface itself.

- The "Password" setting specifies the password used to protect the web interface.
    - This password is not encrypted, nor does it protect the security of the physical device running Cortex.
- The "Auto Refresh" setting determines how the main dashboard will automatically refresh with information from Predator.
    - The "Server" option will cause refreshes to be triggered at a regular interval by an automatic refresh tag attached to relevant pages on the server side.
    - The "Client" option will cause refreshes to be triggered at a regular interval by a client-side refresh script.
        - This option depends on JavaScript being supported and enabled by your browser.
    - The "Off" option disables the auto-refresh altogether.
- The "Heartbeat Threshold" setting determines how many seconds the instance needs to stop responding for before Cortex considers it to be inactive.
    - On slower devices, this value should be raised to prevent long processing times from causing Cortex to mistakenly believe the instance isn't running.
    - On faster devices, this value can be lowered to make the control interface more responsive.
    - It's better to err on the side of too high, since values that are too low can lead to unexpected behavior, like multiple instances running at once.
- The "Theme" setting determines the aesthetic theme that the web interface uses.
    - This setting is strictly visual, and doesn't influence functionality in any significant way.
- The "Preview Display" setting determines whether or not Cortex will show a preview of the current image being processed by Predator.
- The "Show Guesses" setting determines whether or not Cortex will show each guess associated with each plate it displays.

The "Connection Settings" section contains settings relating to the connection between Cortex and the Predator instance.

- The "Execution User" is the user account on the system that will be used to start Predator. If you've previously been using Predator from the command line, you can determine the user you've been executing Predator as by running the `whoami` command.
- The "Instance Directory" setting should be used to specify the absolute directory path of the Predator instance directory.
- The "Image Stream" setting is an absolute file path the points to the image that will be show in the image preview, if enabled.


## Usage

At this point, Cortex should be fully configured, and there shouldn't be any errors on the main dashboard.

### Controlling

Controlling the linked Predator instance is extremely simple. The steps below assume Predator is not already running.

1. On the main Cortex dashboard, click the "Start" button to start Predator.
2. If auto-refresh is enabled, you should see the heart-beat status update within a few seconds.
    - If auto-refresh is disabled, you should manually refresh the page after a few seconds instead.
    - Do not click the "Start" button multiple times rapidly, since this can cause multiple instances to be launched.
3. Once Predator is running, you'll be able to see diagnostics regarding it's status, the plates that have been recently detected, and any errors that may have been reported by the instance.
    - At this point, the web interface can be closed without stopping the instance.
4. To stop the instance, simply press the "Stop" button on the main dashboard.
    - This button will stop all Python processes, even if multiple instances of Predator were inadvertently launched.

### Views

The main dashboard has 3 separate views.

- The "Control" view shows the time since the last heart-beat was issued by the Predator instance.
    - A heart-beat is issued at the end of each processing cycle, and is used to indicate to external programs, like Cortex, that the instance is alive.
- The "Plates" view shows all license plates that last detected, if any.
    - License plates are displayed in a list, along with all of the available guesses and confidence levels.
- The "Errors" view shows any errors reported by the instance.
    - To clarify, errors shown here relate to the Predator instance itself, not Cortex.


## Management

Cortex allows you to manage Predator administratively from the web interface.

### Configuration

Cortex has two separate methods for configuring the instance. Under normal circumstances, the 'basic' method is the more reliable, safer option.

1. Click the "Settings" button on the main Cortex dashboard.
2. Click the "Instance Settings" button on the settings page.
3. Adjust settings as desired.

The settings here correspond to settings in the instance configuration file. These values are described in the documentation for Predator.

### Recovery

Cortex contains multiple tools designed to help diagnose and solve issues with the instance. These tools can help solve problems without physically accessing the device, but they can also cause problems in themselves if not used correctly.

To access the instance recovery page, follow these steps.

1. Click the "Settings" button on the main Cortex dashboard.
2. Click the "Instance Settings" button on the Settings page.
2. Click the "Recovery" button on the Instance Settings page.

There are four main sections on the recovery page.

- The "Configuration" tool allows for the instance configuration file to be directly modified.
    - This allows for advanced settings to be changed, but also increases the risk of configuration corruption.
- The "Diagnostics" tools can display information regarding various aspects of the instance.
    - The "Print Instance Configuration" button will display the raw configuration file for the instance.
    - The "Print Instance Directory" button will display the contents of the instance directory.
    - The "Print Interface Directory" button will display the contents of the interface directory of the instance.
- The "Back-up" tool manages back-ups of the instance configuration file.
    - The "List" section displays a list of all back-ups.
        - Entries in this list can be clicked to autofill them in the functions in the "Manage" section.
    - The "Manage" section provides functions to manage back-ups.
        - The "Create Backup" button creates a back-up of the current state of the instance configuration file.
        - The "View Backup" button displays the contents of a particular back-up file.
        - The "Delete Backup" button deletes a particular back-up file.
        - The "Restore Backup" button restores a particular back-up file to the instance configuration file.
            - This operation overwrites the current configuration file of the instance.
- The "Rescue" tool overwrites the instance configuration file with one from a remote source.
    - This tool can be used rescue a severely corrupted instance, and doesn't depend on the instance configuration file existing at all.
    - To use this tool, enter a URL to a JSON file you'd like to clone to the instance, then click "Flash".
        - For example, `https://domain.tld/restore/config.json`.
