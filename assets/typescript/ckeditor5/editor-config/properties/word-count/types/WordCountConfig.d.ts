export interface WordCountConfig {
  wordCount: {
    onUpdate: (stats: {
      characters: number;
      words: number;
    }) => void | undefined;
  };
}
