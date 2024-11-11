export default class CkWordCountUpdater {
  private minPostLengthLimit: number;
  private statCharacters: number;
  private wordCountContainer: HTMLDivElement | null;
  private wordsCountBox: HTMLSpanElement | null;
  private progressCircle: SVGCircleElement | null;
  private charactersBox: SVGTextElement | null;
  private submitButtons: NodeListOf<HTMLButtonElement>;
  private editorTabLink: HTMLAnchorElement | null;
  private contentHeaderHelp: HTMLDivElement | null;
  private eaBadgeDanger: HTMLSpanElement | null | undefined;
  private customBadgeDanger: HTMLSpanElement;

  constructor(minPostLengthLimit: number) {
    this.minPostLengthLimit = this.getMinPostLengthLimit(minPostLengthLimit);
    this.wordCountContainer = document.querySelector<HTMLDivElement>(
      "#ck-word-count .ck-update"
    );
    this.wordsCountBox = document.querySelector<HTMLSpanElement>(
      ".ck-update__words .count"
    );
    this.progressCircle = document.querySelector<SVGCircleElement>(
      ".ck-update__chart__circle"
    );
    this.charactersBox = document.querySelector<SVGTextElement>(
      ".ck-update__chart__characters"
    );
    this.submitButtons = document.querySelectorAll<HTMLButtonElement>(
      'button[type="submit"]'
    );
    this.editorTabLink = document.querySelector<HTMLAnchorElement>(
      "#tablist-tab-post-content-label"
    );
    this.contentHeaderHelp = document.querySelector<HTMLDivElement>(
      "#tab-post-content-label .content-header-help"
    );
    this.eaBadgeDanger = this.editorTabLink?.querySelector(
      ".badge.badge-danger"
    );
    this.customBadgeDanger = this.createCustomBadgeDanger();
  }

  public updateStats(stats: { characters: number; words: number }): void {
    this.statCharacters = stats.characters;
    const circleRadius: number = Number(this.progressCircle?.getAttribute("r"));
    const circleCircumference: number = Math.floor(2 * Math.PI * circleRadius);
    const charactersProgress: number =
      (stats.characters / this.minPostLengthLimit) * circleCircumference;
    const circleDashArray: number = Math.min(
      charactersProgress,
      circleCircumference
    );
    const isMinLimitReached: boolean =
      stats.characters >= this.minPostLengthLimit;
    const isCloseToMinLimit: boolean =
      !isMinLimitReached && stats.characters > this.minPostLengthLimit * 0.8;

    if (this.progressCircle) {
      this.progressCircle.setAttribute(
        "stroke-dasharray",
        `${circleDashArray},${circleCircumference}`
      );
    }

    if (this.charactersBox) {
      this.charactersBox.textContent = !isMinLimitReached
        ? `${stats.characters - this.minPostLengthLimit}`
        : `${stats.characters}`;
    }

    if (this.wordsCountBox) {
      this.wordsCountBox.textContent = `${stats.words}`;
    }

    if (this.wordCountContainer) {
      this.wordCountContainer.classList.toggle(
        "ck-update__limit-not-reached",
        !isMinLimitReached
      );
      this.wordCountContainer.classList.toggle(
        "ck-update__limit-close",
        isCloseToMinLimit
      );
    }
  }

  public handlePostLengthValidation(): void {
    this.submitButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        if (this.isMinPostLengthConstraintViolated()) {
          this.addError();
          event.preventDefault();
          event.stopPropagation();
        } else {
          this.removeError();
        }
      });
    });
  }

  private getMinPostLengthLimit(minPostLengthLimit: number): number {
    const defaultMinPostLengthLimit = 500;

    try {
      if (minPostLengthLimit < defaultMinPostLengthLimit) {
        throw new Error(
          `Minimum post length limit is not set correctly, so it is set to ${defaultMinPostLengthLimit} characters by default.`
        );
      }

      return minPostLengthLimit;
    } catch (error) {
      console.warn(error);
      return defaultMinPostLengthLimit;
    }
  }

  private createCustomBadgeDanger(): HTMLSpanElement {
    const customBadgeDanger = document.createElement("span");
    customBadgeDanger.classList.add("custom-badge", "custom-badge-danger");
    customBadgeDanger.textContent = "1";

    return customBadgeDanger;
  }

  private isMinPostLengthConstraintViolated(): boolean {
    return this.statCharacters < this.minPostLengthLimit;
  }

  private addError(): void {
    if (
      !this.editorTabLink ||
      (this.eaBadgeDanger && this.editorTabLink.contains(this.eaBadgeDanger))
    ) {
      return;
    }

    if (!this.editorTabLink.classList.contains("has-error")) {
      this.editorTabLink.classList.add("has-error");
    }

    if (!this.editorTabLink.contains(this.customBadgeDanger)) {
      this.editorTabLink.appendChild(this.customBadgeDanger);
    }

    if (
      this.contentHeaderHelp &&
      this.contentHeaderHelp.style.color !== "var(--color-danger)"
    ) {
      this.contentHeaderHelp.style.color = "var(--color-danger)";
    }
  }

  private removeError(): void {
    if (!this.editorTabLink) return;

    if (this.editorTabLink.classList.contains("has-error")) {
      this.editorTabLink.classList.remove("has-error");
    }

    if (this.eaBadgeDanger && this.editorTabLink.contains(this.eaBadgeDanger)) {
      this.editorTabLink.removeChild(this.eaBadgeDanger);
    }

    if (this.editorTabLink.contains(this.customBadgeDanger)) {
      this.editorTabLink.removeChild(this.customBadgeDanger);
    }

    if (
      this.contentHeaderHelp &&
      this.contentHeaderHelp.style.color === "var(--color-danger)"
    ) {
      this.contentHeaderHelp.style.color = "var(--form-tabs-help-color)";
    }
  }
}
