export interface HeadingConfig {
  heading: {
    options: Array<HeadingOption> | undefined;
  };
}

export interface HeadingOption {
  model: string;
  view: string;
  title: string;
  class: string;
}
