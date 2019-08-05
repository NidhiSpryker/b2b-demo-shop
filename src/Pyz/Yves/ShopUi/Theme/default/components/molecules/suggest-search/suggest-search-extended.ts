import SuggestSearch from 'ShopUi/components/molecules/suggest-search/suggest-search';
import OverlayBlock from '../../atoms/overlay-block/overlay-block';

export default class SuggestSearchExtended extends SuggestSearch {
    protected overlay: OverlayBlock;

    protected readyCallback(): void {
        this.overlay = <OverlayBlock>document.querySelector(this.overlaySelector);
        super.readyCallback();
    }

    showSugestions(): void {
        this.suggestionsContainer.classList.remove('is-hidden');
        this.searchInput.classList.add(`${this.name}__input--active`);
        this.hintInput.classList.add(`${this.name}__hint--active`);

        if (window.innerWidth >= this.overlayBreakpoint) {
            this.overlay.showOverlay('no-search', 'no-search');
        }
    }

    hideSugestions(): void {
        this.suggestionsContainer.classList.add('is-hidden');
        this.searchInput.classList.remove(`${this.name}__input--active`);
        this.hintInput.classList.remove(`${this.name}__hint--active`);

        if (window.innerWidth >= this.overlayBreakpoint) {
            this.overlay.hideOverlay('no-search', 'no-search');
        }
    }

    get overlaySelector(): string {
        return '.js-overlay-block';
    }

    get overlayBreakpoint(): number {
        return Number(this.getAttribute('overlay-breakpoint'));
    }

}