package sv.edu.udb.Controladores;

import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.security.SecurityRequirement;
import io.swagger.v3.oas.annotations.tags.Tag;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;
import sv.edu.udb.Modelos.User;
import sv.edu.udb.Servicios.UserService;
import sv.edu.udb.dto.response.UserResponseDto;

import java.util.List;
import java.util.stream.Collectors;

@RequiredArgsConstructor
@Tag(name = "Usuarios", description = "Endpoints para la gestión de usuarios")
@RestController
@RequestMapping("/api/users")
public class UserController {

    private final UserService userService;

    @Operation(summary = "Listar todos los usuarios", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @GetMapping
    public ResponseEntity<List<UserResponseDto>> listarUsuarios() {
        List<User> users = userService.listarUsuarios();
        List<UserResponseDto> responseDtos = users.stream()
                .map(this::toResponseDto)
                .collect(Collectors.toList());
        return ResponseEntity.ok(responseDtos);
    }

    private UserResponseDto toResponseDto(User user) {
        return UserResponseDto.builder()
                .idUser(user.getIdUser())
                .username(user.getUsername())
                .firstname(user.getFirstname())
                .lastname(user.getLastname())
                .age(user.getAge())
                .build();
    }
}
