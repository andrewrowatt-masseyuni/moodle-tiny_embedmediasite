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
 * TODO describe module ui
 *
 * @module     tiny_embedmediasite/ui
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import EmbedMediasiteModal from './modal';
import Templates from 'core/templates';
import {exception as displayException} from 'core/notification';
import {getMyMediasitePresentations} from './repository';

/**
 * Handle action
 *
 * @param {TinyMCE} editor
 */
export const handleAction = async(editor) => {
    displayDialogue(editor);
};

/**
 * Display modal
 *
 * @param  {TinyMCE} editor
 */
const displayDialogue = async(editor) => {
    let page = 1; // Track which "page" of data to load

    // Get first page of presentations. The template (mostly) handles the case of zero presentations.
    let presentations = await getMyMediasitePresentations(page)
            .catch((error) => displayException(error));

    // Show modal with buttons.
    const modal = await EmbedMediasiteModal.create({
        templateContext: presentations,
        large: true,
        removeOnClose: true,
    });

    await modal.show();

    const contentContainer = document.getElementById('tiny_embedmediasite_content-container');
    contentContainer.addEventListener('click', async event => {
        const target = event.target;
        if (target && target.classList.contains('tiny-embedmediasite-insert-button')) {
            const {html} = await Templates.renderForPromise(
                'tiny_embedmediasite/_embedlink', {
                    source: target.dataset.source,
                    title: target.dataset.title
                });
            editor.insertContent(html);
            modal.destroy();
        }
    });

    const loadingIndicator = document.getElementById('tiny_embedmediasite_loading');
    const noMoreContentIndicator = document.getElementById('tiny_embedmediasite_no_more_content');
    const refreshThumbnailsButton = document.getElementById('tiny_embedmediasite_refresh_thumbnails');

    refreshThumbnailsButton.addEventListener('click', () => {
        // Refresh all images in the content container.
        const images = contentContainer.querySelectorAll('img');
        images.forEach(img => {
            const src = img.src;
            img.src = '';
            img.src = src;
        });
    });

    /**
     * Load the second and subsequent pages of content.
     *
     * @param {*} pageNumber
     * @return {number} Number of presentations loaded
     */
    async function loadMoreContent(pageNumber) {
        // Get a page of presentations.
        const presentations = await getMyMediasitePresentations(pageNumber)
            .catch((error) => displayException(error));

        if (!presentations.list?.length) {
            // Short circuit if no presentations.
            return 0;
        }

        // Render and append the new presentations.
        const {html, js} = await Templates.renderForPromise(
            'tiny_embedmediasite/_presentations',
            presentations
        );
        Templates.appendNodeContents('#tiny_embedmediasite_content-container', html, js);
        return presentations.list.length;
    }

    // Set up the Intersection Observer
    const observer = new IntersectionObserver(async entries => {
        // Check if the loading indicator is visible
        if (entries[0].isIntersecting) {
            // Stop observing temporarily to prevent multiple calls while loading
            observer.unobserve(loadingIndicator);

            page++;
            if (await loadMoreContent(page)) {
                // Re-observe the indicator after fetch completes
                observer.observe(loadingIndicator);
            } else {
                // No more data to load; hide the loading indicator
                loadingIndicator.style.display = 'none';
                noMoreContentIndicator.style.display = 'block';
            }
        }
    }, {
        root: null, // Observe the viewport
        threshold: 1.0, // Trigger when 100% of the indicator is visible
        rootMargin: '0px'
    });

    // Start observing the loading indicator element
    observer.observe(loadingIndicator);
};
