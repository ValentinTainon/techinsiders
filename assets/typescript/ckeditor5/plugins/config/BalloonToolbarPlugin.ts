import BalloonToolbarConfig from "./types/BalloonToolbarPlugin";

export default class BalloonToolbarPlugin {
  getConfig(): BalloonToolbarConfig {
    return {
      balloonToolbar: [
        "bold",
        "italic",
        "|",
        "link",
        "insertImage",
        "|",
        "bulletedList",
        "numberedList",
      ],
    };
  }
}
