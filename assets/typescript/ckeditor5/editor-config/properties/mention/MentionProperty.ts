import { MentionConfig } from "./types/MentionConfig";

export class MentionProperty {
  static getConfig(): MentionConfig {
    return {
      mention: {
        feeds: [
          {
            marker: "@",
            feed: [
              /* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
            ],
          },
        ],
      },
    };
  }
}
