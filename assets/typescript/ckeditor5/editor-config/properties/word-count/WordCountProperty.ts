import { WordCountConfig } from "./types/WordCountConfig";
import { trans, WORDCOUNT_STATS_WORDS } from "../../../../translator.ts";

type Stats = { characters: number; words: number };

export class WordCountProperty {
  static getConfig(minCharacters: number): WordCountConfig {
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
          const charactersProgress: number =
            (stats.characters / minCharacters) * circleCircumference;
          const circleDashArray: number = Math.min(
            charactersProgress,
            circleCircumference
          );
          const isMinLimitReached: boolean = stats.characters >= minCharacters;
          const isCloseToMinLimit: boolean =
            !isMinLimitReached && stats.characters > minCharacters * 0.8;

          if (wordsBox) {
            wordsBox.textContent = trans(
              WORDCOUNT_STATS_WORDS,
              { "%stats_words%": stats.words },
              "ckeditor5"
            );
          }

          if (progressCircle) {
            progressCircle.setAttribute(
              "stroke-dasharray",
              `${circleDashArray},${circleCircumference}`
            );
          }

          if (charactersBox) {
            charactersBox.textContent = !isMinLimitReached
              ? `${stats.characters - minCharacters}`
              : `${stats.characters}`;
          }

          if (wordCountContainer) {
            wordCountContainer.classList.toggle(
              "ck-update__limit-not-reached",
              !isMinLimitReached
            );
            wordCountContainer.classList.toggle(
              "ck-update__limit-close",
              isCloseToMinLimit
            );
          }
        },
      },
    };
  }
}
