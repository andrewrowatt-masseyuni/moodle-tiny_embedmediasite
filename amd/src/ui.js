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
    let currentFilter = '';
    let searchGeneration = 0;

    // Show modal immediately with loading state (empty context).
    const modal = await EmbedMediasiteModal.create({
        templateContext: {},
        large: true,
        removeOnClose: true,
    });

    await modal.show();

    const contentContainer = document.getElementById('tiny_embedmediasite_content-container');
    const loadingIndicator = document.getElementById('tiny_embedmediasite_loading');
    const noMoreContentIndicator = document.getElementById('tiny_embedmediasite_no_more_content');
    const noContentFoundIndicator = document.getElementById('tiny_embedmediasite_no_content_found');
    const filterInput = document.getElementById('tiny_embedmediasite_filter');
    const searchButton = document.getElementById('tiny_embedmediasite_search');
    const clearButton = document.getElementById('tiny_embedmediasite_clear');

    contentContainer.addEventListener('click', async event => {
        const target = event.target;
        if (target && target.classList.contains('tiny-embedmediasite-insert-button')) {
            event.preventDefault();
            const mode = target.dataset.mode;
            const container = target.closest('.presentation');
            const source = container.dataset.source;
            const id = container.dataset.id;
            const title = container.dataset.title;
            const descriptionposition =
                container.querySelectorAll(`input[name="${id}description"][type="radio"]:checked`)[0]?.value;
            const includedescription = descriptionposition && descriptionposition !== 'no';
            const includedescriptionabove = descriptionposition === 'above';
            const includedescriptionbelow = descriptionposition === 'below';
            const description = includedescription ? container.querySelectorAll('.description p')[0]?.innerText : '';

            let templateName;
            if (mode === 'linkonly') {
                templateName = 'tiny_embedmediasite/_linkonly';
            } else {
                // Default to embed mode
                templateName = 'tiny_embedmediasite/_embedlink';
            }

            const {html} = await Templates.renderForPromise(
                templateName, {
                    source: source,
                    title: title,
                    description: description,
                    includedescriptionabove: includedescriptionabove,
                    includedescriptionbelow: includedescriptionbelow,
                });
            editor.insertContent(html);
            modal.destroy();
        }
    });

    /**
     * Load the second and subsequent pages of content.
     *
     * @param {number} pageNumber
     * @param {number} generation
     * @return {number} Number of presentations loaded
     */
    async function loadMoreContent(pageNumber, generation) {
        // Get a page of presentations.
        const presentations = await getMyMediasitePresentations(pageNumber, currentFilter)
            .catch((error) => displayException(error));

        // Discard stale results from a previous search.
        if (generation !== searchGeneration) {
            return 0;
        }

        if (!presentations?.list?.length) {
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
            if (await loadMoreContent(page, searchGeneration)) {
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

    // Load the first page of presentations now that the modal is visible.
    if (await loadMoreContent(page, searchGeneration)) {
        observer.observe(loadingIndicator);
    } else {
        loadingIndicator.style.display = 'none';
        noContentFoundIndicator.style.display = 'block';
    }

    // Enable search button only when filter has 3 or more characters. Show clear button when there is text.
    filterInput.addEventListener('input', () => {
        searchButton.disabled = filterInput.value.length < 3;
        clearButton.style.display = filterInput.value.length > 0 ? '' : 'none';
    });

    /**
     * Execute a search with the current filter text.
     */
    async function executeSearch() {
        currentFilter = filterInput.value.trim();
        page = 1;
        searchGeneration++;
        const generation = searchGeneration;

        // Reset the UI.
        contentContainer.innerHTML = '';
        noMoreContentIndicator.style.display = 'none';
        noContentFoundIndicator.style.display = 'none';
        loadingIndicator.style.display = 'block';
        observer.unobserve(loadingIndicator);
        searchButton.disabled = true;

        const results = await getMyMediasitePresentations(page, currentFilter)
            .catch((error) => displayException(error));

        // Discard if a newer search has started.
        if (generation !== searchGeneration) {
            return;
        }

        searchButton.disabled = filterInput.value.length < 3;

        if (!results?.list?.length) {
            loadingIndicator.style.display = 'none';
            noContentFoundIndicator.style.display = 'block';
            return;
        }

        // Render results.
        const {html, js} = await Templates.renderForPromise(
            'tiny_embedmediasite/_presentations',
            results
        );
        Templates.appendNodeContents('#tiny_embedmediasite_content-container', html, js);

        // Re-observe for infinite scroll.
        observer.observe(loadingIndicator);
    }

    // Search button click handler.
    searchButton.addEventListener('click', executeSearch);

    // Clear button resets the filter and re-fetches all presentations.
    clearButton.addEventListener('click', () => {
        const hadFilter = currentFilter !== '';
        filterInput.value = '';
        clearButton.style.display = 'none';
        searchButton.disabled = true;
        if (hadFilter) {
            executeSearch();
        }
    });

    // Allow Enter key to trigger search when button is enabled.
    filterInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !searchButton.disabled) {
            event.preventDefault();
            executeSearch();
        }
    });
};
