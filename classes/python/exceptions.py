from classes.python.exit_codes import LEXICAL_ERROR, SYNTAX_ERROR, XML_FORMAT_ERROR, INTERN_ERROR, SEMANTIC_ERROR, OPERAND_TYPE_ERROR


class ApplicationError(Exception):

    def __init__(self, message: str, exit_code: int):
        self._message = message
        self._exit_code = exit_code

    def get_message(self):
        return self._message

    def get_exit_code(self):
        return self._exit_code


class LexicalError(ApplicationError):

    def __init__(self, message: str):
        super().__init__(message, LEXICAL_ERROR)


class SrcSyntaxError(ApplicationError):

    def __init__(self, message: str):
        super().__init__(message, SYNTAX_ERROR)


class XMLFormatError(ApplicationError):

    def __init__(self, message: str):
        super().__init__(message, XML_FORMAT_ERROR)


class InternalError(ApplicationError):

    def __init__(self, message: str):
        super().__init__(message, INTERN_ERROR)


class SemanticError(ApplicationError):

    def __init__(self, message: str):
        super().__init__(message, SEMANTIC_ERROR)


class OperandTypeError(ApplicationError):

    def __init__(self, message: str):
        super().__init__(message, OPERAND_TYPE_ERROR)
