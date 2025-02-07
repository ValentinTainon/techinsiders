import { StyleConfig } from "./types/StyleConfig";
import {
  trans,
  PLUGIN_STYLE_CATEGORY,
  PLUGIN_STYLE_TITLE,
  PLUGIN_STYLE_SUBTITLE,
  PLUGIN_STYLE_CODE_BRIGHT,
  PLUGIN_STYLE_CODE_DARK,
  PLUGIN_STYLE_INFO_BOX,
  PLUGIN_STYLE_SIDE_QUOTE,
  PLUGIN_STYLE_MARKER,
  PLUGIN_STYLE_SPOILER,
} from "../../../../translator.ts";

export class StyleProperty {
  static getConfig(): StyleConfig {
    return {
      style: {
        definitions: [
          {
            name: trans(PLUGIN_STYLE_TITLE, {}, "ckeditor5"),
            element: "h2",
            classes: ["document-title"],
          },
          {
            name: trans(PLUGIN_STYLE_CATEGORY, {}, "ckeditor5"),
            element: "h3",
            classes: ["category"],
          },
          {
            name: trans(PLUGIN_STYLE_SUBTITLE, {}, "ckeditor5"),
            element: "h3",
            classes: ["document-subtitle"],
          },
          {
            name: trans(PLUGIN_STYLE_CODE_BRIGHT, {}, "ckeditor5"),
            element: "pre",
            classes: ["fancy-code", "fancy-code-bright"],
          },
          {
            name: trans(PLUGIN_STYLE_CODE_DARK, {}, "ckeditor5"),
            element: "pre",
            classes: ["fancy-code", "fancy-code-dark"],
          },
          {
            name: trans(PLUGIN_STYLE_INFO_BOX, {}, "ckeditor5"),
            element: "p",
            classes: ["info-box"],
          },
          {
            name: trans(PLUGIN_STYLE_SIDE_QUOTE, {}, "ckeditor5"),
            element: "blockquote",
            classes: ["side-quote"],
          },
          {
            name: trans(PLUGIN_STYLE_MARKER, {}, "ckeditor5"),
            element: "span",
            classes: ["marker"],
          },
          {
            name: trans(PLUGIN_STYLE_SPOILER, {}, "ckeditor5"),
            element: "span",
            classes: ["spoiler"],
          },
        ],
      },
    };
  }
}
