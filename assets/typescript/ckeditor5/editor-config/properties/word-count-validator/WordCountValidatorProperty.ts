import { WordCountValidatorConfig } from "./types/WordCountValidatorConfig";

export class WordCountValidatorProperty {
  static getConfig(
    minCharacters: number | undefined,
    maxCharacters: number | undefined
  ): WordCountValidatorConfig {
    return {
      wordCountValidator: {
        minCharacters: minCharacters,
        maxCharacters: maxCharacters,
      },
    };
  }
}
