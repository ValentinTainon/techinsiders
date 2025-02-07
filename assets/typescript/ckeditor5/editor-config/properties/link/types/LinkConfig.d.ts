export interface LinkConfig {
  link: {
    addTargetToExternalLinks: boolean | undefined;
    defaultProtocol: string | undefined;
    decorators: {
      toggleDownloadable: {
        mode: string;
        label: string;
        attributes: {
          download: string;
        };
      };
    };
  };
}
