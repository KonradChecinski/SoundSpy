import torch
from torchvision import transforms as tt
from Klasy_Modeli import MusicGenrePretrainedDenseNet169
from PIL import Image
import json
import time
import numpy as np
import os
os.environ['MPLCONFIGDIR'] = "/var/www/app/storage/app/python/tmp/"
os.environ[ 'NUMBA_CACHE_DIR' ] = '/tmp/'
import matplotlib.pyplot as plt
import librosa
import librosa.display
from pydub import AudioSegment
import sys
import uuid
import pathlib

# start = time.time()

DESIRED_ASPECT_RATIO = 1.5
WIDTH = 12
DURATION = 30.0
TARGET_SAMPLE_RATE = 22050
HEIGHT = WIDTH / DESIRED_ASPECT_RATIO
FILE = sys.argv[1]

# file_test = r'recordings\HIP-HOP\recording_Broke Boys.wav'


def convert(filename: str, from_format: str, to_format: str):
    raw_audio = AudioSegment.from_file(
        f"{filename}+{from_format}", format=from_format)
    raw_audio.export(f"{filename}+{to_format}", format=to_format)


def apply_low_pass_filter(audio, cutoff_freq):
    return audio.low_pass_filter(cutoff_freq)


if FILE[:-3] == 'mp3':
    convert(FILE, 'mp3', 'wav')
elif FILE[:-3] == 'm4a':
    convert(FILE, 'm4a', 'wav')
else:
    pass

audio = AudioSegment.from_file(FILE)
cutoff_frequency = 2500
filtered_audio = apply_low_pass_filter(audio, cutoff_frequency)

os.makedirs('/var/www/app/storage/app/python/DENOISED_AUDIO', exist_ok=True)
song_name = str(f'{uuid.uuid4()}.wav')



filtered_audio.export(os.path.join(
    '/var/www/app/storage/app/python/DENOISED_AUDIO', song_name), format="wav")

y, sr = librosa.load(os.path.join('/var/www/app/storage/app/python/DENOISED_AUDIO', song_name))
y_resampled = librosa.resample(
    y, orig_sr=sr, target_sr=TARGET_SAMPLE_RATE, res_type='kaiser_best')

if librosa.get_duration(y=y, sr=sr) > DURATION:
    start_time = max(0, librosa.get_duration(y=y, sr=sr) / 2 - DURATION / 2)
    end_time = start_time + DURATION
    y = y[int(start_time * sr):int(end_time * sr)]

S = librosa.feature.melspectrogram(y=y, sr=sr)
S_DB = librosa.amplitude_to_db(S, ref=np.max)
plt.figure(figsize=(4, 3))
librosa.display.specshow(S_DB, sr=sr, hop_length=512)
plt.tick_params(left=False, right=False, labelleft=False,
                labelbottom=False, bottom=False)

os.makedirs('/var/www/app/storage/app/python/SPECTROGRAMS', exist_ok=True)

spectrogram_name = str(uuid.uuid4())
plt.savefig(f"{os.path.join('/var/www/app/storage/app/python/SPECTROGRAMS',spectrogram_name)}.png",
            bbox_inches='tight', pad_inches=0)

img = Image.open(f"{os.path.join('/var/www/app/storage/app/python/SPECTROGRAMS',spectrogram_name)}.png")

newsize = (432, 288)
img = img.resize(newsize)

img.save(f"{os.path.join('/var/www/app/storage/app/python/SPECTROGRAMS',spectrogram_name)}.png")
plt.close()


transform = tt.Compose([
    tt.ToTensor()
])

top_n = 5

class_labels = {
    0: 'Classical',
    1: 'Disco',
    2: 'EDM',
    3: 'Funk',
    4: 'Heavy metal',
    5: 'Hip Hop',
    6: 'Jazz',
    7: 'POP',
    8: 'Reggae',
    9: 'Rock',
    10: 'Techno'
}

image_path = f"{os.path.join('/var/www/app/storage/app/python/SPECTROGRAMS',spectrogram_name)}.png"
input_image = Image.open(image_path)
image_check = transform(input_image)
image_rgb = input_image.convert("RGB")
input_data = transform(image_rgb).unsqueeze(0)

checkpoint = torch.load(rf'{pathlib.Path(__file__).parent.resolve()}' + '/models/best_DenseNet_169_checkpoint79.pth', map_location=torch.device('cpu'))
model_state_dict = checkpoint['model_state_dict']
loaded_model = MusicGenrePretrainedDenseNet169()
loaded_model.load_state_dict(model_state_dict)
loaded_model.eval()


with torch.no_grad():
    predictions = loaded_model(input_data)
    probabilities = torch.nn.functional.softmax(predictions, dim=1)

    top_n_probabilities, top_n_indices = torch.topk(
        probabilities, top_n, dim=1)

    predictions_dict = {}

    for prob, idx in zip(top_n_probabilities[0], top_n_indices[0]):
        predicted_class_index = idx.item()
        predicted_class_label = class_labels[predicted_class_index]
        probability = prob.item()

        '{:f}'.format(probability)
        probability = probability * 100
        predictions_dict[predicted_class_label] = round(probability, 4)

    # Save predictions as JSON
    output_json_path = 'predictions.json'
    with open(output_json_path, 'w') as json_file:
        json.dump(predictions_dict, json_file)

    pretty_json = json.dumps(predictions_dict, indent=4)
    print(pretty_json)

# end = time.time()
# print("Total time:", end - start)
