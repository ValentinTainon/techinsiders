// @ts-ignore
import { ClassicEditor } from 'ckeditor5';
import ClassicEditorConfig from './ClassicEditorConfig.ts';

class ClassicEditorInitializer {

    public async init(): Promise<void> {
        const htmlElementToInitEditor: HTMLTextAreaElement | null = document.querySelector<HTMLTextAreaElement>('textarea#editor');
        
        if (htmlElementToInitEditor) {
            try {
                await ClassicEditor.create(
                    htmlElementToInitEditor, 
                    new ClassicEditorConfig(htmlElementToInitEditor.className).config()
                );
            } catch (error) {
                console.error('Failed to initialize editor ->', error);
            }
        }
    }
}

new ClassicEditorInitializer().init();