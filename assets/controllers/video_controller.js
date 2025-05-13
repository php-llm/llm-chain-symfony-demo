import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);

        this.video = document.getElementById('videoFeed');
        this.canvas = document.getElementById('canvas');

        const input = document.getElementById('chat-message');
        input.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                this.submitMessage();
            }
        });
        input.focus();

        const submitButton = document.getElementById('chat-submit');
        submitButton.addEventListener('click', (event) => {
            this.submitMessage();
        });

        this.video.srcObject = await navigator.mediaDevices.getUserMedia({video: true, audio: false});
    };

    submitMessage() {
        const input = document.getElementById('chat-message');
        const instruction = input.value;
        const image = this.captureImage();

        this.component.action('submit', { instruction, image });
        input.value = '';
    }

    captureImage() {
        this.canvas.width = this.video.videoWidth;
        this.canvas.height = this.video.videoHeight;
        const context = this.canvas.getContext('2d');
        context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);
        return this.canvas.toDataURL('image/jpeg', 0.8);
    }
}
