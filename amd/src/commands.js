// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tiny embedmediasite commands
 *
 * @module     tiny_embedmediasite/commands
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getButtonImage} from 'editor_tiny/utils';
import {get_string as getString} from 'core/str';
import {component, buttonName, icon} from 'tiny_embedmediasite/common';
import Notification from 'core/notification';

import {getMyMediasitePresentations} from './repository';

export const getSetup = async() => {
    const [
        buttonTitle,
        buttonImage,
    ] = await Promise.all([
        getString('buttontitle', component),
        getButtonImage('icon', component),
    ]);

    return (editor) => {
        // Register the embedmediasite icon.
        editor.ui.registry.addIcon(icon, buttonImage.html);

        // Register the toolbar Button.
        editor.ui.registry.addButton(buttonName, {
            icon,
            tooltip: buttonTitle,
            onAction: () => {
                // TODO do the action when toolbar button is pressed.
                const page = 1;
                getMyMediasitePresentations(page).then(function(data) {
                    window.console.log('Received data from getMyMediasitePresentations:');
                    window.console.log(data);
                    return data;
                }).catch(function(err) {
                    window.console.log('Error occurred while fetching presentations:');
                    window.console.log(err);
                });

                // window.console.log(response);

                Notification.alert("Plugin tiny_embedmediasite", "You just pressed a toolbar button 3");

            },
        });

        // Register the Menu item.
        editor.ui.registry.addMenuItem(buttonName, {
            icon,
            text: buttonTitle,
            onAction: () => {
                // TODO do the action when item is selected from the menu.
                Notification.alert("Plugin tiny_embedmediasite", "You just selected an item from a menu");
            },
        });
    };
};
