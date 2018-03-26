from enum import Enum


class Frame(Enum):
    TF = 0
    LF = 1
    GF = 2

    @staticmethod
    def str_to_frame(frame_str: str):
        if frame_str == 'TF':
            return Frame.TF
        if frame_str == 'LF':
            return Frame.LF
        else:
            return Frame.GF

    def __str__(self):
        if self is Frame.TF:
            return 'TF'
        if self is Frame.LF:
            return 'LF'
        else:
            return 'GF'
