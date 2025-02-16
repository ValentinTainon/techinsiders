export class EditorWordCounter {
  private minLengthLimit: number;
  private statCharacters: number;
  private wordCountContainer: HTMLDivElement | null;
  private wordsCountBox: HTMLSpanElement | null;
  private progressCircle: SVGCircleElement | null;
  private charactersBox: SVGTextElement | null;
  private postContentTabLink: HTMLAnchorElement | null;
  private tabPostContent: HTMLDivElement | null;
  private ckeditorField: HTMLDivElement | null | undefined;
  private ckeditorFieldHelp: HTMLElement | null | undefined;
  private submitButtons: NodeListOf<HTMLButtonElement>;

  constructor(minLengthLimit: number) {
    this.minLengthLimit = this.getMinLengthLimit(minLengthLimit);
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
    this.ckeditorField = this.tabPostContent?.querySelector<HTMLDivElement>(
      "div.field-ckeditor5"
    );
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
      (stats.characters / this.minLengthLimit) * circleCircumference;
    const circleDashArray: number = Math.min(
      charactersProgress,
      circleCircumference
    );
    const isMinLimitReached: boolean = stats.characters >= this.minLengthLimit;
    const isCloseToMinLimit: boolean =
      !isMinLimitReached && stats.characters > this.minLengthLimit * 0.8;

    if (this.progressCircle) {
      this.progressCircle.setAttribute(
        "stroke-dasharray",
        `${circleDashArray},${circleCircumference}`
      );
    }

    if (this.charactersBox) {
      this.charactersBox.textContent = !isMinLimitReached
        ? `${stats.characters - this.minLengthLimit}`
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

  public handleContentLengthValidation(): void {
    this.submitButtons?.forEach((button) => {
      button.addEventListener("click", (event: MouseEvent) => {
        const isMinConstraintViolated = this.isMinLengthConstraintViolated();

        if (isMinConstraintViolated) event.preventDefault();

        this.handleClassErrorOnCkeditorField(isMinConstraintViolated);
        const numberOfFieldsInError = this.getNumberOfFieldsInError();

        if (isMinConstraintViolated || numberOfFieldsInError > 0) {
          this.handleBadgeDanger(numberOfFieldsInError);
        }
      });
    });
  }

  private getMinLengthLimit(minLengthLimit: number): number {
    const defaultMinLengthLimit = 500;

    try {
      if (minLengthLimit < defaultMinLengthLimit) {
        throw new Error(
          `Minimum post length limit is not set correctly, so it is set to ${defaultMinLengthLimit} characters by default.`
        );
      }

      return minLengthLimit;
    } catch (error) {
      console.warn(error);
      return defaultMinLengthLimit;
    }
  }

  private handleClassErrorOnCkeditorField(
    isMinConstraintViolated: boolean
  ): void {
    this.ckeditorField?.classList.toggle("has-error", isMinConstraintViolated);
    this.ckeditorFieldHelp?.classList.toggle(
      "help-error",
      isMinConstraintViolated
    );
  }

  private isMinLengthConstraintViolated(): boolean {
    return this.statCharacters < this.minLengthLimit;
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
