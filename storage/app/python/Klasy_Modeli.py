import torch.nn as nn
import torchvision.models as models
import torch.nn.functional as F


class ImageClassificationBase(nn.Module):
    # training step
    def training_step(self, batch):
        img, targets = batch
        out = self(img)
        loss = F.nll_loss(out, targets)
        return loss

    # validation step
    def validation_step(self, batch):
        img, targets = batch
        out = self(img)
        loss = F.nll_loss(out, targets)
        acc = accuracy(out, targets)
        return {'val_acc': acc.detach(), 'val_loss': loss.detach()}

    # validation epoch end
    def validation_epoch_end(self, outputs):
        batch_losses = [x['val_loss'] for x in outputs]
        epoch_loss = torch.stack(batch_losses).mean()
        batch_accs = [x['val_acc'] for x in outputs]
        epoch_acc = torch.stack(batch_accs).mean()
        return {'val_loss': epoch_loss.item(), 'val_acc': epoch_acc.item()}

    # print result end epoch
    def epoch_end(self, epoch, result):
        print("Epoch [{}] : train_loss: {:.4f}, val_loss: {:.4f}, val_acc: {:.4f}".format(
            epoch, result["train_loss"], result["val_loss"], result["val_acc"]))


class MusicGenreClassificationCNN(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = nn.Sequential(
            nn.Conv2d(3, 8, 3, stride=1, padding=1),
            nn.BatchNorm2d(num_features=8),
            nn.ReLU(),

            nn.Conv2d(8, 16, 3, stride=1, padding=1),
            nn.BatchNorm2d(num_features=16),
            nn.ReLU(),
            nn.MaxPool2d(2, 2),

            nn.Conv2d(16, 32, 3, stride=1, padding=1),
            nn.BatchNorm2d(num_features=32),
            nn.ReLU(),

            nn.Conv2d(32, 64, 3, stride=1, padding=1),
            nn.BatchNorm2d(num_features=64),
            nn.ReLU(),
            nn.MaxPool2d(2, 2),

            nn.Conv2d(64, 128, 3, stride=1, padding=1),
            nn.BatchNorm2d(num_features=128),
            nn.ReLU(),

            nn.Conv2d(128, 256, 3, stride=1, padding=1),
            nn.BatchNorm2d(num_features=256),
            nn.ReLU(),
            nn.MaxPool2d(2, 2),


            nn.Flatten(),
            nn.Linear(54*36*256, 512),
            nn.ReLU(),
            nn.Linear(512, 120),
            nn.Dropout(p=0.3, inplace=False),
            nn.LogSoftmax(dim=1),
        )

    def forward(self, xb):
        return self.network(xb)


class MusicGenrePretrainedResnet50(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = models.resnet50(
            weights='ResNet50_Weights.IMAGENET1K_V1')
        # Replace last layer
        num_ftrs = self.network.fc.in_features
        self.network.fc = nn.Sequential(
            nn.Linear(num_ftrs, 120),
            nn.LogSoftmax(dim=1)
        )

    def forward(self, xb):
        return self.network(xb)


class MusicGenrePretrainedInceptionV3(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = models.inception_v3(
            weights='Inception_V3_Weights.IMAGENET1K_V1')
        # Replace last layer
        num_ftrs = self.network.fc.in_features
        self.network.fc = nn.Sequential(
            nn.Linear(num_ftrs, 120),
            nn.LogSoftmax(dim=1)
        )

    def forward(self, xb):
        return self.network(xb)


class MusicGenrePretrainedVGG16(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = models.vgg16('VGG16_Weights.DEFAULT')
        # Replace last layer
        self.network.classifier = nn.Sequential(
            nn.Linear(in_features=25088, out_features=4096, bias=True),
            nn.ReLU(inplace=True),
            nn.Dropout(p=0.5, inplace=False),
            nn.Linear(in_features=4096, out_features=4096, bias=True),
            nn.ReLU(inplace=True),
            nn.Dropout(p=0.5, inplace=False),
            nn.Linear(in_features=4096, out_features=120, bias=True),
            nn.LogSoftmax(dim=1)
        )

    def forward(self, xb):
        return self.network(xb)


class MusicGenrePretrainedGoogleNet(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = models.googlenet(weights='GoogLeNet_Weights.DEFAULT')
        # Replace last layer
        num_ftrs = self.network.fc.in_features
        self.network.fc = nn.Sequential(
            nn.Linear(num_ftrs, 120),
            nn.LogSoftmax(dim=1)
        )

    def forward(self, xb):
        return self.network(xb)


class MusicGenrePretrainedResnet152(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = models.resnet152(weights='ResNet152_Weights.DEFAULT')
        # Replace last layer
        num_ftrs = self.network.fc.in_features
        self.network.fc = nn.Sequential(
            nn.Linear(num_ftrs, 120),
            nn.LogSoftmax(dim=1)
        )

    def forward(self, xb):
        return self.network(xb)


class MusicGenrePretrainedDenseNet169(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = models.densenet169(
            weights='DenseNet169_Weights.DEFAULT')
        # Replace last layer
        num_ftrs = self.network.classifier.in_features
        self.network.classifier = nn.Sequential(
            nn.Linear(num_ftrs, 120),
            nn.LogSoftmax(dim=1)
        )

    def forward(self, xb):
        return self.network(xb)


class MusicGenrePretrainedDenseNet201(ImageClassificationBase):
    def __init__(self):
        super().__init__()

        self.network = models.densenet201(
            weights='DenseNet201_Weights.IMAGENET1K_V1')
        # Replace last layer
        num_ftrs = self.network.classifier.in_features
        self.network.classifier = nn.Sequential(
            nn.Linear(num_ftrs, 120),
            nn.LogSoftmax(dim=1)
        )

    def forward(self, xb):
        return self.network(xb)
