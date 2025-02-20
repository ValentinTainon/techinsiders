import { WordCountValidatorConfig } from "./types/WordCountValidatorConfig";

export class WordCountValidatorProperty {
  static getConfig(
    minCharacters: number | undefined = undefined,
    maxCharacters: number | undefined = undefined
  ): WordCountValidatorConfig {
    return {
      wordCountValidator: {
        minCharacters: minCharacters,
        maxCharacters: maxCharacters,
      },
    };
  }
}
