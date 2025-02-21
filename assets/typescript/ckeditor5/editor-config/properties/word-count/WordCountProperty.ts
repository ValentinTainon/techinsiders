import { WordCountConfig } from "./types/WordCountConfig";
import { trans, WORDCOUNT_STATS_WORDS } from "../../../../translator.ts";

type Stats = { characters: number; words: number };

export class WordCountProperty {
  static getConfig(
    minCharacters: number | undefined,
    maxCharacters: number | undefined
  ): WordCountConfig {
    return {
      wordCount: {
        onUpdate: (stats: Stats) => {
          const wordCountContainer: HTMLDivElement | null =
            document.querySelector<HTMLDivElement>(".ck-word-count-container");

          if (!wordCountContainer) return;

          const wordsBox =
            wordCountContainer.querySelector<HTMLSpanElement>(
              ".ck-update__words"
            );
          const charactersBox =
            wordCountContainer.querySelector<SVGTextElement>(
              ".ck-update__chart__characters"
            );

          if (wordsBox) {
            wordsBox.textContent = trans(
              WORDCOUNT_STATS_WORDS,
              { "%stats_words%": stats.words },
              "ckeditor5"
            );
          }

          // Characters
          let isCloseToMinLimit: boolean = false;
          let isCloseToMaxLimit: boolean = false;
          let isMinLimitReached: boolean = true;
          let isMaxLimitExceeded: boolean = false;

          if (minCharacters) {
            const progressCircle =
              wordCountContainer.querySelector<SVGCircleElement>(
                ".ck-update__chart__circle"
              );
            const circleRadius: number = Number(
              progressCircle?.getAttribute("r")
            );
            const circleCircumference: number = Math.floor(
              2 * Math.PI * circleRadius
            );
            const minCharactersProgress: number =
              (stats.characters / minCharacters) * circleCircumference;
            const circleDashArray: number = Math.min(
              minCharactersProgress,
              circleCircumference
            );

            isMinLimitReached = stats.characters >= minCharacters;
            isCloseToMinLimit =
              !isMinLimitReached && stats.characters > minCharacters * 0.8;

            progressCircle?.setAttribute(
              "stroke-dasharray",
              `${circleDashArray},${circleCircumference}`
            );
          }

          if (maxCharacters) {
            isMaxLimitExceeded = stats.characters > maxCharacters;
            isCloseToMaxLimit =
              !isMaxLimitExceeded && stats.characters > maxCharacters * 0.8;
          }

          if (charactersBox) {
            if (minCharacters && !isMinLimitReached) {
              charactersBox.textContent = `${stats.characters - minCharacters}`;
            } else {
              charactersBox.textContent = `${stats.characters}`;
            }
          }

          wordCountContainer.classList.toggle(
            "ck-update__limit-has-error",
            !isMinLimitReached || isMaxLimitExceeded
          );
          wordCountContainer.classList.toggle(
            "ck-update__limit-close",
            isCloseToMinLimit || isCloseToMaxLimit
          );
        },
      },
    };
  }
}
