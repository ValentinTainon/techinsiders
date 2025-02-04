import CodeBlockConfig from "./types/CodeBlockPlugin";

export default class CodeBlockPlugin {
  getConfig(): CodeBlockConfig {
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
    };
  }
}
