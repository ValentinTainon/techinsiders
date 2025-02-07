import { LinkConfig } from "./types/LinkConfig";

export class LinkProperty {
  static getConfig(): LinkConfig {
    return {
      link: {
        addTargetToExternalLinks: true,
        defaultProtocol: "https://",
        decorators: {
          toggleDownloadable: {
            mode: "manual",
            label: "Downloadable",
            attributes: {
              download: "file",
            },
          },
        },
      },
    };
  }
}
