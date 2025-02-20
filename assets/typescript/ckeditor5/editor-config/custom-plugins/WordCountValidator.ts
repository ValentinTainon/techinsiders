// @ts-ignore
import { Plugin, WordCount } from "ckeditor5";

type Stats = { characters: number; words: number };

export class WordCountValidator extends Plugin {
  public static get pluginName() {
    return "WordCountValidator" as const;
  }

  public static get requires() {
    return [WordCount];
  }

  public init(): void {
    try {
      // @ts-ignore
      const editor = this.editor;
      const wordCountValidatorConfig = editor.config.get("wordCountValidator");
      const minCharacters: number = Number(
        wordCountValidatorConfig.minCharacters
      );
      const maxCharacters: number = Number(
        wordCountValidatorConfig.maxCharacters
      );

      let charactersStats: Stats["characters"];

      editor.plugins
        .get("WordCount")
        .on("update", (event: Event, stats: Stats) => {
          charactersStats = stats.characters;
        });

      const submitButtons = document.querySelectorAll<HTMLButtonElement>(
        "button.action-save[type=submit]"
      );

      submitButtons?.forEach((button) => {
        button.addEventListener("click", (event: MouseEvent) => {
          const hasError: boolean =
            charactersStats < minCharacters || charactersStats > maxCharacters;

          if (hasError) {
            event.preventDefault();
          }

          this.handleClassErrorOnCkeditorField(hasError);
          const numberOfFieldsInError = this.getNumberOfFieldsInError();

          if (hasError || numberOfFieldsInError > 0) {
            this.handleBadgeDanger(numberOfFieldsInError);
          }
        });
      });
    } catch (error) {
      console.error(error);
    }
  }

  private handleClassErrorOnCkeditorField(hasError: boolean): void {
    const ckeditorField = document.querySelector<HTMLDivElement>(
      "div.field-ckeditor5"
    );
    const ckeditorFieldHelp =
      ckeditorField?.querySelector<HTMLElement>("small.form-help");

    ckeditorField?.classList.toggle("has-error", hasError);
    ckeditorFieldHelp?.classList.toggle("help-error", hasError);
  }

  private getNumberOfFieldsInError(): number {
    const tabPostContent = document.querySelector<HTMLDivElement>(
      "div#tab-post-content-label"
    );

    if (tabPostContent) {
      return tabPostContent.querySelectorAll<HTMLDivElement>(
        "div.form-group.has-error"
      ).length;
    } else {
      throw new Error("Post content tab not found");
    }
  }

  private handleBadgeDanger(numberOfFieldsInError: number): void {
    const postContentTabLink = document.querySelector<HTMLAnchorElement>(
      "a#tablist-tab-post-content-label"
    );
    const badgeDanger: HTMLSpanElement | null | undefined =
      postContentTabLink?.querySelector("span.badge-danger");

    if (badgeDanger && numberOfFieldsInError > 0) {
      badgeDanger.textContent = `${numberOfFieldsInError}`;
    } else if (!badgeDanger && numberOfFieldsInError > 0) {
      postContentTabLink?.appendChild(this.createBadgeDanger());
    } else if (badgeDanger && numberOfFieldsInError <= 0) {
      postContentTabLink?.removeChild(badgeDanger);
    }
  }

  private createBadgeDanger(): HTMLSpanElement {
    const customBadgeDanger = document.createElement("span");
    customBadgeDanger.classList.add("badge", "badge-danger");
    customBadgeDanger.textContent = "1";

    return customBadgeDanger;
  }
}
