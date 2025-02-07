export interface StyleConfig {
  style: {
    definitions: Array<StyleDefinition> | undefined;
  };
}

export interface StyleDefinition {
  name: string;
  element: string;
  classes: Array<string>;
}
