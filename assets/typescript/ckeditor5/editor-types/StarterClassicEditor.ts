import {
  AccessibilityHelp,
  Bold,
  ClassicEditor,
  Code,
  CodeBlock,
  Essentials,
  Italic,
  Link,
  Mention,
  Paragraph,
  // @ts-ignore
} from "ckeditor5";

// Styles
import "ckeditor5/dist/ckeditor5.css";
import "../../../styles/ckeditor5/custom.css";

// Properties
import { CodeBlockProperty } from "../editor-config/properties/code-block/CodeBlockProperty.ts";
import { LanguageProperty } from "../editor-config/properties/language/LanguageProperty.ts";
import { LicenseKeyProperty } from "../editor-config/properties/license-key/LicenseKeyProperty.ts";
import { LinkProperty } from "../editor-config/properties/link/LinkProperty.ts";
import { MentionProperty } from "../editor-config/properties/mention/MentionProperty.ts";
import { PlaceholderProperty } from "../editor-config/properties/placeholder/PlaceholderProperty.ts";
import { RemovePluginsProperty } from "../editor-config/properties/remove-plugins/RemovePluginsProperty.ts";
import { ToolbarProperty } from "../editor-config/properties/toolbar/ToolbarProperty.ts";
import { TranslationsProperty } from "../editor-config/properties/translations/TranslationsProperty.ts";

export class StarterClassicEditor extends ClassicEditor {
  public static builtinPlugins = [
    AccessibilityHelp,
    Bold,
    Code,
    CodeBlock,
    Essentials,
    Italic,
    Link,
    Mention,
    Paragraph,
  ];

  private static toolbarItems: Array<string> = [
    "undo",
    "redo",
    "|",
    "bold",
    "italic",
    "link",
    "|",
    "code",
    "codeBlock",
    "|",
    "accessibilityHelp",
  ];

  public static getDefaultConfig(isFrLocale: boolean) {
    return {
      ...CodeBlockProperty.getConfig(),
      ...LanguageProperty.getConfig(isFrLocale),
      ...LicenseKeyProperty.getConfig(),
      ...LinkProperty.getConfig(),
      ...MentionProperty.getConfig(),
      ...PlaceholderProperty.getConfig(),
      ...RemovePluginsProperty.getConfig(),
      ...ToolbarProperty.getConfig(StarterClassicEditor.toolbarItems),
      ...TranslationsProperty.getConfig(isFrLocale),
    };
  }
}
