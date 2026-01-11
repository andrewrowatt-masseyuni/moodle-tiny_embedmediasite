[![Moodle Plugin CI](https://github.com/andrewrowatt-masseyuni/moodle-tiny_embedmediasite/actions/workflows/moodle-ci.yml/badge.svg)](https://github.com/andrewrowatt-masseyuni/moodle-tiny_embedmediasite/actions/workflows/moodle-ci.yml)
# Embed Mediasite Video

A TinyMCE editor plugin for Moodle that enables embedding Mediasite videos directly in the editor using a convenient button interface.

## Description

The Embed Mediasite Video plugin adds a button to the TinyMCE editor toolbar that lets instructors easily embed Mediasite videos into course content. When the button is clicked, users can browse their Mediasite content and select a video to embed. Once inserted, videos are displayed as links in the editor, but when saved and viewed will render as embedded videos if the companion plugin ([media_mediasite](https://github.com/andrewrowatt-masseyuni/moodle-media_mediasite)) is installed.

This plugin requires the MyMediasite repository plugin ([repository_mymediasite](https://github.com/andrewrowatt-masseyuni/moodle-repository_mymediasite)) to function.

## Installing via uploaded ZIP file

1.  Log in to your Moodle site as an admin and go to *Site administration \> Plugins \> Install plugins*.
2.  Upload the ZIP file with the plugin code. You should only be prompted to add extra details if your plugin type is not automatically detected.
3.  Check the plugin validation report and finish the installation.

## Installing manually

The plugin can be also installed by putting the contents of this directory to

```
{your/moodle/dirroot}/lib/editor/tiny/plugins/embedmediasite
```

Afterwards, log in to your Moodle site as an admin and go to *Site administration \> Notifications* to complete the installation.

Alternatively, you can run

```
$ php admin/cli/upgrade.php
```

to complete the installation from the command line.

## License

2026 Andrew Rowatt [A.J.Rowatt@massey.ac.nz](mailto:A.J.Rowatt@massey.ac.nz)

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.

