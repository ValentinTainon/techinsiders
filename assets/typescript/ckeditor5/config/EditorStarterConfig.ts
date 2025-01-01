import {
  AccessibilityHelp,
  Bold,
  Code,
  CodeBlock,
  Essentials,
  Italic,
  Link,
  Mention,
  Paragraph,
  // @ts-ignore
} from "ckeditor5";
// @ts-ignore
import frTranslations from "ckeditor5/translations/fr.js";
import StarterConfigType from "../interface/StarterConfigType.ts";

export default class EditorStarterConfig {
  private isDefaultLocale: boolean;

  constructor() {
    this.isDefaultLocale =
      document.documentElement.getAttribute("lang") === "fr";
  }

  public getConfig(): StarterConfigType {
    return {
      codeBlock: {
        languages: [
          { language: "bash", label: "Bash" },
          { language: "c", label: "C" },
          { language: "cpp", label: "C++" },
          { language: "cs", label: "C#" },
          { language: "css", label: "CSS" },
          { language: "html", label: "HTML" },
          { language: "java", label: "Java" },
          { language: "javascript", label: "JavaScript" },
          { language: "php", label: "PHP" },
          { language: "plaintext", label: "Plain text" },
          { language: "python", label: "Python" },
          { language: "typescript", label: "TypeScript" },
        ],
      },
      language: {
        ui: this.isDefaultLocale ? "fr" : "en",
        content: this.isDefaultLocale ? "fr" : "en",
      },
      licenseKey: "GPL",
      link: {
        addTargetToExternalLinks: true,
        defaultProtocol: "https://",
      },
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
      placeholder: this.isDefaultLocale
        ? "Tapez votre contenu ici !"
        : "Type your content here!",
      plugins: [
        AccessibilityHelp,
        Bold,
        Code,
        CodeBlock,
        Essentials,
        Italic,
        Link,
        Mention,
        Paragraph,
      ],
      toolbar: {
        items: [
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
        ],
      },
      translations: this.isDefaultLocale ? frTranslations : null,
    };
  }
}
