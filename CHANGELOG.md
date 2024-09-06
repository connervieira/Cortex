# Changelog

This document contains a list of all the changes for each version of Cortex.


## Version 1.0 

### Initial Release

- Completed basic functionality.


## Version 1.1

December 5th, 2023

- Reduced default heartbeat interval.
- Updated image preview to support Predator version 9.0.
- Updated error message display to support Predator version 10.0.
- Added more resilient error checking.


## Version 2.0

*Release date to be determined*

- Made error messages in `config.php` more descriptive.
- Updated instance configuration system.
    - Cortex can now configure both Predator Fabric and vanilla Predator.
- Reduced the height of the "control" display on the main dashboard.
- Updated the `start.sh` script used to launch Predator to automatically start into the correct mode.
- Removed the interface directory configuration option in favor of automatically detecting it from the connected Predator configuration file.
- Cortex now kills ALPR processes when stopping Predator.
- The instance recovery tools now support both Predator Fabric and vanilla Predator.
- Added advanced management tools.
    - Predator's real-time ALPR functionality can now be registered as a SystemD service from the Cortex interface.
    - Cortex now allows users to view the contents of files in the interface, instance, and working directories.
    - Added tool to view and download the plate history log in various formats.
- It is now possible to configure Cortext to not display each guess for each plate.
- Cortex now displays plates involved in alerts with a red background.
