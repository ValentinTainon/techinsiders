import { ImageConfig } from "./types/ImageConfig";

export class ImageProperty {
  static getConfig(): ImageConfig {
    return {
      image: {
        toolbar: [
          "toggleImageCaption",
          "imageTextAlternative",
          "|",
          "imageStyle:wrapText",
          "imageStyle:breakText",
          "|",
          "linkImage",
          "|",
          "resizeImage",
        ],
      },
    };
  }
}
