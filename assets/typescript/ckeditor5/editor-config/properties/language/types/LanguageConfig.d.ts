export interface LanguageConfig {
  language: {
    ui: string | undefined;
    content: string | undefined;
    textPartLanguage: Array<TextPartLanguageOption> | undefined;
  };
}

export interface TextPartLanguageOption {
  title: string;
  languageCode: string;
}
