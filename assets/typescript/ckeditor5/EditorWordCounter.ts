export default class EditorWordCounter {
  private minPostLengthLimit: number;
  private statCharacters: number;
  private wordCountContainer: HTMLDivElement | null;
  private wordsCountBox: HTMLSpanElement | null;
  private progressCircle: SVGCircleElement | null;
  private charactersBox: SVGTextElement | null;
  private badgeDanger: HTMLSpanElement | null;
  private postContentTabLink: HTMLAnchorElement | null;
  private tabPostContent: HTMLDivElement | null;
  private ckeditorField: HTMLDivElement | null | undefined;
  private ckeditorFieldHelp: HTMLElement | null | undefined;
  private submitButtons: NodeListOf<HTMLButtonElement>;

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
    this.postContentTabLink = document.querySelector<HTMLAnchorElement>(
      "a#tablist-tab-post-content-label"
    );
    this.tabPostContent = document.querySelector<HTMLDivElement>(
      "div#tab-post-content-label"
    );
    this.ckeditorField =
      this.tabPostContent?.querySelector<HTMLDivElement>("div.field-ckeditor");
    this.ckeditorFieldHelp =
      this.ckeditorField?.querySelector<HTMLElement>("small.form-help");
    this.submitButtons = document.querySelectorAll<HTMLButtonElement>(
      "button.action-save[type=submit]"
    );
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
    this.submitButtons?.forEach((button) => {
      button.addEventListener("click", (event: MouseEvent) => {
        const isConstraintViolated = this.isMinPostLengthConstraintViolated();

        if (isConstraintViolated) event.preventDefault();

        this.handleClassErrorOnCkeditorField(isConstraintViolated);
        const numberOfFieldsInError = this.getNumberOfFieldsInError();

        if (isConstraintViolated || numberOfFieldsInError > 0) {
          this.handleBadgeDanger(numberOfFieldsInError);
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

  private handleClassErrorOnCkeditorField(isConstraintViolated: boolean): void {
    this.ckeditorField?.classList.toggle("has-error", isConstraintViolated);
    this.ckeditorFieldHelp?.classList.toggle(
      "help-error",
      isConstraintViolated
    );
  }

  private isMinPostLengthConstraintViolated(): boolean {
    return this.statCharacters < this.minPostLengthLimit;
  }

  private getNumberOfFieldsInError(): number {
    if (this.tabPostContent) {
      return this.tabPostContent.querySelectorAll<HTMLDivElement>(
        "div.form-group.has-error"
      ).length;
    } else {
      throw new Error("Post content tab not found");
    }
  }

  private handleBadgeDanger(numberOfFieldsInError: number): void {
    const badgeDanger: HTMLSpanElement | null | undefined =
      this.postContentTabLink?.querySelector("span.badge-danger");

    if (badgeDanger && numberOfFieldsInError > 0) {
      badgeDanger.textContent = `${numberOfFieldsInError}`;
    } else if (!badgeDanger && numberOfFieldsInError > 0) {
      this.postContentTabLink?.appendChild(this.createBadgeDanger());
    } else if (badgeDanger && numberOfFieldsInError <= 0) {
      this.postContentTabLink?.removeChild(badgeDanger);
    }
  }

  private createBadgeDanger(): HTMLSpanElement {
    const customBadgeDanger = document.createElement("span");
    customBadgeDanger.classList.add("badge", "badge-danger");
    customBadgeDanger.textContent = "1";

    return customBadgeDanger;
  }
}
