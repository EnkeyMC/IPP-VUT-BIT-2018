from enum import Enum

__author__ = "Martin Omacht"
__copyright__ = "Copyright 2018"
__credits__ = ["Martin Omacht"]


class Frame(Enum):
    """
    Enumeration of frame types
    """
    TF = 0
    LF = 1
    GF = 2

    @staticmethod
    def str_to_frame(frame_str: str):
        """
        Get enumeration frame type from string
        :param frame_str: frame type in string form
        :return: frame type
        """
        if frame_str == 'TF':
            return Frame.TF
        if frame_str == 'LF':
            return Frame.LF
        else:
            return Frame.GF

    def __str__(self):
        """
        Get frame type string representation
        :return: frame type string form
        """
        if self is Frame.TF:
            return 'TF'
        if self is Frame.LF:
            return 'LF'
        else:
            return 'GF'
