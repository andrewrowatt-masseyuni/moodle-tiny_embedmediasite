# Embed Mediasite Video #

A TinyMCE editor plugin for Moodle that enables embedding Mediasite videos directly in the editor using a convenient button interface.

## Description ##

The Embed Mediasite Video plugin provides a button in the TinyMCE editor toolbar that allows instructors to easily embed Mediasite videos into course content. When the button is clicked, users can browse their Mediasite content from the MyMediasite repository and select videos to embed. The plugin displays video metadata including duration and provides a user-friendly interface for searching and selecting content. Once inserted, videos are displayed as links in the editor but render as embedded iframes when the content is saved and viewed. This plugin requires the MyMediasite repository plugin (repository_mymediasite) to function.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/lib/editor/tiny/plugins/embedmediasite

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.