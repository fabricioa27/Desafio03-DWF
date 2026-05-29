package sv.edu.udb.dto.request;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class AuthRequestDto {

    @NotBlank(message = "El nombre de usuario no puede ser nulo")
    private String username;
    @NotBlank(message = "La contraseña no puede estar vacia")
    @Size(min = 8, message = "La contraseña debe tener al menos 8 caracteres")
    private String password;
}
