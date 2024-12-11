export default interface StarterConfigType {
  codeBlock: { languages: { language: string; label: string }[] };
  language: { ui: string; content: string };
  link: { addTargetToExternalLinks: boolean; defaultProtocol: string };
  mention: { feeds: { marker: string; feed: string[] }[] };
  placeholder: string;
  plugins: any[];
  toolbar: { items: string[] };
  translations: any | null;
}
