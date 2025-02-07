export interface CodeBlockConfig {
  codeBlock: {
    languages: Array<CodeBlockLanguageDefinition> | undefined;
  };
}

export interface CodeBlockLanguageDefinition {
  language: string;
  label: string;
}
