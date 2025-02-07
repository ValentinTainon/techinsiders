import { WordCountConfig } from "./types/WordCountConfig";
import EditorWordCounter from "../../../utils/EditorWordCounter";

export class WordCountProperty {
  static getConfig(editorWordCounter: EditorWordCounter): WordCountConfig {
    return {
      wordCount: {
        onUpdate: (stats: { characters: number; words: number }) => {
          editorWordCounter.updateStats(stats);
        },
      },
    };
  }
}
