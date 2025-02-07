export interface HtmlSupportConfig {
  htmlSupport: {
    allow: Array<MatcherObjectPattern> | undefined;
  };
}

export interface MatcherObjectPattern {
  name: RegExp;
  styles: boolean;
  attributes: boolean;
  classes: boolean;
}
