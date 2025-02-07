import { BalloonToolbarConfig } from "./types/BalloonToolbarConfig";

export class BalloonToolbarProperty {
  static getConfig(): BalloonToolbarConfig {
    return {
      balloonToolbar: {
        items: [
          "bold",
          "italic",
          "|",
          "link",
          "insertImage",
          "|",
          "bulletedList",
          "numberedList",
        ],
        shouldNotGroupWhenFull: true,
      },
    };
  }
}
